<?php

namespace Framework\Supports;

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
