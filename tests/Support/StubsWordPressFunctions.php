<?php

if (!function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default')
    {
        echo __($text, $domain);
    }
}

if (!function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1)
    {
        return true;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('wp_generate_uuid4')) {
    function wp_generate_uuid4()
    {
        return '00000000-0000-4000-8000-000000000000';
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}

if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

if (!function_exists('sanitize_title')) {
    function sanitize_title($title, $fallback_title = '', $context = 'save')
    {
        $title = strtolower((string) $title);
        $title = preg_replace('/[^a-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);

        $title = trim($title, '-');

        return $title === '' ? $fallback_title : $title;
    }
}

if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true)
    {
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);

        if ($data === 'N;') {
            return true;
        }

        if (strlen($data) < 4) {
            return false;
        }

        if ($data[1] !== ':') {
            return false;
        }

        return (bool) preg_match('/^(a|O|s|i|d|b|N):/', $data);
    }
}

if (!function_exists('maybe_serialize')) {
    function maybe_serialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        return $data;
    }
}

if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($data)
    {
        if (is_serialized($data)) {
            return @unserialize($data, ['allowed_classes' => false]);
        }

        return $data;
    }
}
