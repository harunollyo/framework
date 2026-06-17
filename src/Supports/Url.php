<?php
/**
 * WordPress URL helper for building query-string URLs and performing redirects.
 * Uses add_query_arg and wp_safe_redirect with a JavaScript fallback when headers are already sent.
 * Supports redirect_back via wp_get_referer.
 *
 * @package    Framework
 * @subpackage Supports
 * @since      1.0.0
 */
namespace Framework\Supports;

defined('ABSPATH') || exit;

class Url
{
    public static function make($url, $data = [])
    {
        $referer = $url;
        return add_query_arg($data, $referer);
    }

    public static function redirect($url, $data = [])
    {
        $referer = $url;
        $redirect_url = add_query_arg($data, $referer);

        static::perform_redirect($redirect_url);
    }

    public static function redirect_back($data = [])
    {
        $referer = wp_get_referer();
        $redirect_url = add_query_arg($data, $referer);

        static::perform_redirect($redirect_url);
    }

    protected static function perform_redirect($redirect_url)
    {
        if (headers_sent()) {
            $safe_url = esc_url($redirect_url);

            echo '<script>window.location.href = ' . wp_json_encode($safe_url) . ';</script>';
            exit;
        }

        wp_safe_redirect($redirect_url);
        exit;
    }
}
