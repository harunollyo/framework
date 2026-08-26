<?php
/**
 * Redirect response for site route controllers.
 *
 * @package    Framework
 * @subpackage Http
 * @since      1.0.0
 */
namespace Framework\Http;

defined('ABSPATH') || exit;

use Framework\Http\Concerns\InteractsWithCookies;
use Framework\Http\Concerns\InteractsWithFlashData;
use Framework\Managers\SessionManager;

use function Framework\app;

class RedirectResponse
{
    use InteractsWithCookies;
    use InteractsWithFlashData;

    /**
     * The redirect target URL.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected $url;

    /**
     * The HTTP redirect status code.
     *
     * @var int
     *
     * @since 1.0.0
     */
    protected $status;

    /**
     * Create a new RedirectResponse instance.
     *
     * @param string $url The redirect target URL.
     * @param int $status The HTTP status code.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct(string $url, int $status = 302)
    {
        $this->url = $url;
        $this->status = $status;
    }

    /**
     * Get the redirect URL.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function get_url()
    {
        return $this->url;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function get_status()
    {
        return $this->status;
    }

    /**
     * Send the redirect and terminate the request.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function send()
    {
        $this->prepare_send();

        wp_safe_redirect($this->url, $this->status);
        exit;
    }

    /**
     * Persist the session and emit queued cookies before the redirect is issued.
     *
     * Ordering is load-bearing: starting or changing the session queues the
     * identifier cookie, and the flush is what actually emits it. Flushing first
     * would send the redirect without the cookie, orphaning the stored session.
     *
     * Kept separate from send() so the ordering stays testable, since send()
     * itself terminates the request.
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function prepare_send()
    {
        app(SessionManager::class)->save();

        $this->cookie_manager()->flush_queued_cookies();
    }
}
