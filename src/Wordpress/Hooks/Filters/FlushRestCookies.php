<?php
/**
 * WordPress rest_post_dispatch filter that saves the session and emits queued cookies for REST responses.
 * Runs before the REST server sends headers or serializes the body, so cookies still reach the client.
 * Returns the dispatched result untouched so every REST route passes through unchanged.
 *
 * @package    Framework
 * @subpackage Wordpress\Hooks\Filters
 * @since      1.0.0
 */
namespace Framework\Wordpress\Hooks\Filters;

defined('ABSPATH') || exit;

use Framework\Managers\CookieManager;
use Framework\Managers\SessionManager;
use Framework\Wordpress\BaseHook;
use Framework\Wordpress\Constants\HookNames;
use Framework\Wordpress\Constants\HookTypes;

use function Framework\app;

class FlushRestCookies extends BaseHook
{
    /**
     * Get the name.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_name()
    {
        return HookNames::REST_POST_DISPATCH;
    }

    /**
     * Get the type.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_type()
    {
        return HookTypes::FILTER;
    }

    /**
     * Handle.
     *
     * Flushes the cookie queue and returns the dispatched result unchanged.
     *
     * @param mixed $args The positional arguments.
     *
     * @return mixed The unmodified REST response.
     *
     * @since 1.0.0
     */
    public function handle(...$args)
    {
        // Saved before the flush so a queued identifier cookie is emitted with it.
        app(SessionManager::class)->save();

        app(CookieManager::class)->flush_queued_cookies();

        return $args[0] ?? null;
    }
}
