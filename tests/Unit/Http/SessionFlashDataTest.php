<?php

namespace Framework\Tests\Unit\Http;

use Framework\Contracts\SessionHandler;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Managers\CookieManager;
use Framework\Managers\SessionManager;
use Framework\Routing\SiteRouter;
use Framework\Supports\ErrorBag;
use Framework\Tests\Support\Http\RecordingCookieManager;
use Framework\Tests\Support\Session\RecordingSessionHandler;
use Framework\Tests\Support\Session\TestSessionManager;
use Framework\Tests\Unit\TestCase;
use ReflectionMethod;

use function Framework\app;

class SessionFlashDataTest extends TestCase
{
    protected RecordingSessionHandler $handler;

    protected RecordingCookieManager $cookies;

    protected TestSessionManager $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrap_application();

        $this->handler = new RecordingSessionHandler();
        $this->cookies = new RecordingCookieManager();
        $this->cookies->set_default_path_and_domain('/', null, false, 'Lax');
        $this->session = new TestSessionManager($this->handler);

        app()->instance(CookieManager::class, $this->cookies);
        app()->instance(SessionManager::class, $this->session);
        app()->instance(SessionHandler::class, $this->handler);
    }

    protected function request_with(array $attributes, array $files = []): Request
    {
        $request = new Request();
        $request->make_from_http([], $attributes, $files, [], [], []);

        app()->instance(Request::class, $request);
        app()->instance('request', $request);

        return $request;
    }

    // ------------------------------------------------- sensitive field exclusion

    public function test_flashing_input_never_writes_a_password_to_the_driver(): void
    {
        $this->request_with([
            'email' => 'ada@example.test',
            'password' => 'hunter2',
            'password_confirmation' => 'hunter2',
            'current_password' => 'old-secret',
        ]);

        (new RedirectResponse('/back'))->with_input();

        $this->session->save();

        $payload = $this->handler->last_written_payload();
        $old_input = $payload['_old_input'];

        $this->assertSame('ada@example.test', $old_input['email']);
        $this->assertArrayNotHasKey('password', $old_input);
        $this->assertArrayNotHasKey('password_confirmation', $old_input);
        $this->assertArrayNotHasKey('current_password', $old_input);

        // Belt and braces: the secret must appear nowhere in the serialized payload.
        $this->assertStringNotContainsString('hunter2', serialize($payload));
        $this->assertStringNotContainsString('old-secret', serialize($payload));
    }

    public function test_flashing_input_never_writes_an_uploaded_file_to_the_driver(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'framework-session-test');
        file_put_contents($tmp, 'png-bytes');

        $this->request_with(
            ['caption' => 'holiday'],
            ['avatar' => [
                'name' => 'me.png',
                'type' => 'image/png',
                'tmp_name' => $tmp,
                'error' => 0,
                'size' => filesize($tmp),
            ]]
        );

        (new RedirectResponse('/back'))->with_input();
        $this->session->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame(['caption' => 'holiday'], $payload['_old_input']);
        $this->assertArrayNotHasKey('avatar', $payload['_old_input']);

        foreach ($this->flatten($payload) as $value) {
            $this->assertNotInstanceOf(\Framework\Filesystem\UploadedFile::class, $value);
        }

        @unlink($tmp);
    }

    public function test_an_explicit_input_array_is_still_filtered(): void
    {
        $this->request_with([]);

        (new RedirectResponse('/back'))->with_input(['email' => 'ada@example.test', 'password' => 'hunter2']);
        $this->session->save();

        $payload = $this->handler->last_written_payload();

        $this->assertArrayNotHasKey('password', $payload['_old_input']);
        $this->assertStringNotContainsString('hunter2', serialize($payload));
    }

    public function test_the_never_flash_list_is_extendable_by_configuration(): void
    {
        $this->assertContains('password', $this->session->get_never_flash());
        $this->assertContains('password_confirmation', $this->session->get_never_flash());
        $this->assertContains('current_password', $this->session->get_never_flash());
    }

    // --------------------------------------------------------- errors and input

    public function test_errors_are_stored_as_plain_data_and_read_back_through_a_bag(): void
    {
        $this->request_with([]);

        (new RedirectResponse('/back'))->with_errors(['email' => ['Email is invalid.', 'Email is taken.']]);
        $this->session->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame(['email' => ['Email is invalid.', 'Email is taken.']], $payload['errors']);

        foreach ($this->flatten($payload) as $value) {
            $this->assertFalse(is_object($value), 'No object may be written into the session payload.');
        }

        $bag = new ErrorBag($payload['errors']);

        $this->assertTrue($bag->any());
        $this->assertTrue($bag->has('email'));
        $this->assertSame('Email is invalid.', $bag->first('email'));
        $this->assertCount(2, $bag);
        $this->assertFalse($bag->has('name'));
        $this->assertSame('none', $bag->first('name', 'none'));
    }

    public function test_with_flashes_arbitrary_values(): void
    {
        $this->request_with([]);

        (new RedirectResponse('/back'))->with('status', 'Saved!')->with(['tone' => 'success']);
        $this->session->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame('Saved!', $payload['status']);
        $this->assertSame('success', $payload['tone']);
        // save() ages the flash, so keys flashed this request sit in "old" ready
        // for the next request to read.
        $this->assertContains('status', $payload['_flash']['old']);
        $this->assertContains('tone', $payload['_flash']['old']);
        $this->assertSame([], $payload['_flash']['new']);
    }

    // ------------------------------------------------------------ save ordering

    public function test_the_redirect_response_saves_before_flushing_cookies(): void
    {
        $this->request_with([]);

        $redirect = new RedirectResponse('/back');
        $redirect->with('status', 'Saved!');

        $prepare = new ReflectionMethod(RedirectResponse::class, 'prepare_send');
        $prepare->setAccessible(true);
        $prepare->invoke($redirect);

        // If the flush ran first, the identifier cookie would still be queued
        // rather than sent, and the stored session would be unreachable.
        $this->assertCount(1, $this->handler->writes);
        $this->assertContains($this->session->get_name(), $this->cookies->sent_names());
        $this->assertSame([], $this->cookies->get_queued_cookies());
    }

    public function test_the_site_router_saves_before_flushing_cookies(): void
    {
        $this->request_with([]);

        $this->session->put('cart', ['sku']);

        $router = new SiteRouter('test');
        $flush = new ReflectionMethod(SiteRouter::class, 'flush_queued_cookies');
        $flush->setAccessible(true);
        $flush->invoke($router);

        $this->assertCount(1, $this->handler->writes);
        $this->assertContains($this->session->get_name(), $this->cookies->sent_names());
        $this->assertSame([], $this->cookies->get_queued_cookies());
    }

    // ------------------------------------------------------- input isolation

    public function test_session_values_never_reach_request_input(): void
    {
        $request = $this->request_with(['email' => 'ada@example.test']);

        $this->session->put('secret', 'session-only');
        $this->session->flash('errors', ['email' => ['bad']]);

        $this->assertArrayNotHasKey('secret', $request->all());
        $this->assertArrayNotHasKey('errors', $request->all());
        $this->assertArrayNotHasKey('_token', $request->all());
        $this->assertArrayNotHasKey('_flash', $request->all());

        $this->assertNull($request->secret);
        $this->assertNull($request->only('secret'));
        $this->assertNull($request->input('secret'));
        $this->assertSame(['email' => 'ada@example.test'], $request->all());

        // And the sanitized/validated view the validator reads is equally clean.
        $this->assertArrayNotHasKey('secret', $request->attributes());
    }

    public function test_the_request_exposes_the_session_and_its_old_input(): void
    {
        $request = $this->request_with(['email' => 'ada@example.test', 'password' => 'hunter2']);

        $this->assertSame($this->session, $request->session());

        $request->flash();
        $this->session->save();

        $payload = $this->handler->last_written_payload();

        $this->assertSame(['email' => 'ada@example.test'], $payload['_old_input']);
        $this->assertStringNotContainsString('hunter2', serialize($payload));
    }

    public function test_flash_only_and_flash_except_filter_the_input(): void
    {
        $request = $this->request_with(['a' => 1, 'b' => 2, 'password' => 'hunter2']);

        $request->flash_only(['a']);
        $this->assertSame(['a' => 1], $this->session->get_old_input());

        $request->flash_except(['a']);
        $this->assertSame(['b' => 2], $this->session->get_old_input());
    }

    /**
     * Flatten a nested payload into a single list of scalar/object leaves.
     */
    protected function flatten(array $payload): array
    {
        $values = [];

        array_walk_recursive($payload, function ($value) use (&$values) {
            $values[] = $value;
        });

        return $values;
    }
}
