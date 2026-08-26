<?php

namespace Framework\Tests\Support\Session;

use Framework\Managers\SessionManager;

/**
 * A SessionManager double that makes the request cookie, the headers-sent state,
 * and the warning log controllable without touching globals or real headers.
 */
class TestSessionManager extends SessionManager
{
    /**
     * The cookies the client is pretending to have sent.
     *
     * @var array<string,string>
     */
    public array $request_cookies = [];

    /**
     * Whether the headers should report as already sent.
     *
     * @var bool
     */
    public bool $headers_sent = false;

    /**
     * The warnings that were logged.
     *
     * @var array<int,string>
     */
    public array $warnings = [];

    protected function request_cookie(string $name)
    {
        return $this->request_cookies[$name] ?? null;
    }

    protected function headers_already_sent()
    {
        return $this->headers_sent;
    }

    protected function log_warning(string $message)
    {
        $this->warnings[] = $message;
    }
}
