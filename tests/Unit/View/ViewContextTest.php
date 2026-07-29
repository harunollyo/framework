<?php

namespace Framework\Tests\Unit\View;

use Framework\Tests\Unit\TestCase;
use Framework\View\TemplateEngine;
use Framework\View\View;
use Framework\View\ViewContext;

use function Framework\app;
use function Framework\view_data;

class ViewContextTest extends TestCase
{
    /**
     * Temporary views directory.
     *
     * @var string
     */
    protected $views;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootstrap_application();

        $this->views = sys_get_temp_dir() . '/framework-view-context-' . uniqid();
        mkdir($this->views, 0777, true);

        $app = $this->bootstrap_application();
        $app->use_view_path($this->views);
        $app->instance(TemplateEngine::class, new TemplateEngine());
        $app->instance(ViewContext::class, new ViewContext());
    }

    protected function tearDown(): void
    {
        app(ViewContext::class)->clear();
        $this->remove_directory($this->views);
        parent::tearDown();
    }

    public function test_prepare_merges_shared_data_and_activates_context(): void
    {
        $engine = app(TemplateEngine::class);
        $engine->share('site', 'demo');

        $path = $this->views . '/product.php';
        file_put_contents($path, '<?php');

        $view = new View('product', ['id' => 5]);
        $context = app(ViewContext::class);
        $data = $context->prepare($view, 'products.show', $path);

        $this->assertSame(['site' => 'demo', 'id' => 5], $data);
        $this->assertNotNull($context->get_active());
        $this->assertSame('product', $context->get_active()['template']);
    }

    public function test_view_data_returns_values_from_matched_template(): void
    {
        $path = $this->views . '/hello.php';
        file_put_contents(
            $path,
            '<?php echo esc_html((string) \Framework\view_data("name")); echo "-"; echo esc_html((string) \Framework\view_data("missing", "fallback")); echo "-"; echo esc_html(implode(",", array_keys(\Framework\view_data())));'
        );

        $context = app(ViewContext::class);
        $context->prepare(
            new View('hello', ['name' => 'Ada']),
            'hello',
            $path
        );

        ob_start();
        require $path;
        $output = (string) ob_get_clean();

        $this->assertSame('Ada-fallback-name', $output);
    }

    public function test_view_data_supports_dot_notation(): void
    {
        $path = $this->views . '/nested.php';
        file_put_contents(
            $path,
            '<?php echo esc_html((string) \Framework\view_data("product.name")); echo "-"; echo esc_html((string) \Framework\view_data("product.meta.sku", "none"));'
        );

        $context = app(ViewContext::class);
        $context->prepare(
            new View('nested', [
                'product' => [
                    'name' => 'Widget',
                    'meta' => ['sku' => 'W-1'],
                ],
            ]),
            'nested',
            $path
        );

        ob_start();
        require $path;
        $output = (string) ob_get_clean();

        $this->assertSame('Widget-W-1', $output);
    }

    public function test_unauthorized_caller_cannot_read_view_data(): void
    {
        $path = $this->views . '/secret.php';
        file_put_contents($path, '<?php');

        $context = app(ViewContext::class);
        $context->prepare(
            new View('secret', ['token' => 'abc']),
            'secret',
            $path
        );

        $this->assertSame('denied', view_data('token', 'denied'));
        $this->assertSame([], view_data());
    }

    public function test_foreign_template_cannot_read_active_data(): void
    {
        $matched = $this->views . '/matched.php';
        $foreign = $this->views . '/foreign.php';
        file_put_contents($matched, '<?php');
        file_put_contents(
            $foreign,
            '<?php echo esc_html((string) \Framework\view_data("token", "blocked"));'
        );

        $context = app(ViewContext::class);
        $context->prepare(
            new View('matched', ['token' => 'secret']),
            'matched',
            $matched
        );

        ob_start();
        require $foreign;
        $output = (string) ob_get_clean();

        $this->assertSame('blocked', $output);
    }

    public function test_push_pop_isolates_nested_partial_data(): void
    {
        $parent = $this->views . '/parent.php';
        $child = $this->views . '/child.php';
        file_put_contents(
            $parent,
            '<?php echo esc_html((string) \Framework\view_data("label"));'
        );
        file_put_contents(
            $child,
            '<?php echo esc_html((string) \Framework\view_data("label", "missing")); echo "-"; echo esc_html((string) \Framework\view_data("products", "none"));'
        );

        $context = app(ViewContext::class);
        $context->prepare(
            new View('parent', ['label' => 'parent', 'products' => [1]]),
            'parent',
            $parent
        );

        $context->push([
            'template' => 'child',
            'route_name' => '',
            'resolved_path' => $child,
            'data' => array_merge(app(TemplateEngine::class)->get_shared(), ['label' => 'child']),
        ]);

        ob_start();
        require $child;
        $child_output = (string) ob_get_clean();

        $this->assertSame('child-none', $child_output);

        $context->pop();

        ob_start();
        require $parent;
        $parent_output = (string) ob_get_clean();

        $this->assertSame('parent', $parent_output);
    }

    public function test_clear_empties_entire_stack(): void
    {
        $path = $this->views . '/one.php';
        file_put_contents($path, '<?php');

        $context = app(ViewContext::class);
        $context->push([
            'template' => 'one',
            'route_name' => '',
            'resolved_path' => $path,
            'data' => ['a' => 1],
        ]);
        $context->push([
            'template' => 'two',
            'route_name' => '',
            'resolved_path' => $path,
            'data' => ['b' => 2],
        ]);

        $context->clear();

        $this->assertNull($context->get_active());
    }

    protected function remove_directory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                $this->remove_directory($path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
