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

if (!function_exists('esc_url')) {
    function esc_url($url)
    {
        return (string) $url;
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '')
    {
        return 'https://example.test' . '/' . ltrim((string) $path, '/');
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook_name, $value, ...$args)
    {
        return $value;
    }
}

if (!function_exists('locate_template')) {
    function locate_template($template_names, $load = false, $require_once = true, $args = [])
    {
        return '';
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value)
    {
        if (is_array($value)) {
            return array_map('wp_unslash', $value);
        }

        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key)
    {
        $key = strtolower((string) $key);

        return preg_replace('/[^a-z0-9_\-]/', '', $key);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str)
    {
        return trim(strip_tags((string) $str));
    }
}

if (!function_exists('add_rewrite_rule')) {
    function add_rewrite_rule($regex, $query, $after = 'bottom')
    {
        return true;
    }
}

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules($hard = true)
    {
        return true;
    }
}

if (!function_exists('status_header')) {
    function status_header($code)
    {
        return true;
    }
}

if (!function_exists('nocache_headers')) {
    function nocache_headers()
    {
        return true;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = [])
    {
        throw new RuntimeException((string) $message);
    }
}

if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect($location, $status = 302)
    {
        return true;
    }
}

if (!function_exists('get_query_var')) {
    function get_query_var($var, $default = '')
    {
        return $GLOBALS['framework_test_query_vars'][$var] ?? $default;
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return false;
    }
}

if (!function_exists('is_page')) {
    function is_page($page = '')
    {
        return false;
    }
}

if (!function_exists('get_queried_object_id')) {
    function get_queried_object_id()
    {
        return 0;
    }
}

if (!function_exists('get_permalink')) {
    function get_permalink($post = 0)
    {
        return 'https://example.test/?p=' . (int) $post;
    }
}

if (!function_exists('get_page_by_path')) {
    function get_page_by_path($page_path, $output = OBJECT, $post_type = 'page')
    {
        return null;
    }
}

if (!function_exists('wp_is_block_theme')) {
    function wp_is_block_theme()
    {
        return false;
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

if (!function_exists('get_user_by')) {
    function get_user_by($field, $value)
    {
        $users = $GLOBALS['framework_test_users'] ?? [];

        foreach ($users as $user) {
            if (isset($user[$field]) && (string) $user[$field] === (string) $value) {
                return (object) $user;
            }
        }

        return false;
    }
}

if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        protected $method;

        protected $route;

        protected $params;

        protected $headers;

        public function __construct(string $method = 'GET', string $route = '/test', array $params = [], array $headers = [])
        {
            $this->method = $method;
            $this->route = $route;
            $this->params = $params;
            $this->headers = $headers;
        }

        public function get_params()
        {
            return $this->params;
        }

        public function get_file_params()
        {
            return [];
        }

        public function get_method()
        {
            return $this->method;
        }

        public function get_route()
        {
            return $this->route;
        }

        public function get_headers()
        {
            return $this->headers;
        }

        public function get_url_params()
        {
            return $this->params['URL'] ?? [];
        }
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error
    {
        protected $code;

        protected $message;

        protected $data;

        public function __construct($code = '', $message = '', $data = '')
        {
            $this->code = $code;
            $this->message = $message;
            $this->data = $data;
        }

        public function get_error_code()
        {
            return $this->code;
        }

        public function get_error_message()
        {
            return $this->message;
        }

        public function get_error_data($code = '')
        {
            return $this->data;
        }
    }
}
