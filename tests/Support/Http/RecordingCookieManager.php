<?php

namespace Framework\Tests\Support\Http;

use Framework\Http\Cookie;
use Framework\Managers\CookieManager;

/**
 * A CookieManager test double that records emitted cookies instead of sending them.
 *
 * The headers-sent state and the warning log are also captured so the guarded
 * write path can be asserted without touching real headers or the filesystem.
 */
class RecordingCookieManager extends CookieManager
{
    /**
     * The cookies that reached the send step.
     *
     * @var array<int,Cookie>
     */
    public array $sent = [];

    /**
     * The warnings that were logged.
     *
     * @var array<int,string>
     */
    public array $warnings = [];

    /**
     * Whether the headers should report as already sent.
     *
     * @var bool
     */
    public bool $headers_sent = false;

    /**
     * Record the cookie rather than sending it to the browser.
     *
     * @param Cookie $cookie The cookie to send.
     *
     * @return bool
     */
    protected function send_cookie(Cookie $cookie)
    {
        $this->sent[] = $cookie;

        return true;
    }

    /**
     * Report the configured headers-sent state.
     *
     * @return bool
     */
    protected function headers_already_sent()
    {
        return $this->headers_sent;
    }

    /**
     * Record the warning rather than writing it to the log file.
     *
     * @param string $message The message to log.
     *
     * @return void
     */
    protected function log_warning(string $message)
    {
        $this->warnings[] = $message;
    }

    /**
     * Get the names of the cookies that were sent.
     *
     * @return array<int,string>
     */
    public function sent_names(): array
    {
        return array_map(function (Cookie $cookie) {
            return $cookie->get_name();
        }, $this->sent);
    }
}
