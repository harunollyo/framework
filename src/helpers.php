<?php

namespace Framework;

use Closure;
use Framework\Application;
use Framework\AppSettings;
use Framework\Collections\Collection;
use Framework\Database\Migrations\Migrator;
use Framework\Http\Request;
use Framework\Wordpress\User;
use Framework\Http\Response;
use Framework\Supports\Arr;
use Framework\Supports\Facades\Settings;
use Framework\Supports\HigherOrderTapProxy;
use Framework\Supports\Str;
use Framework\Supports\Url;
use Framework\Supports\Utils;

use function Framework\Polyfill\array_key_first;
use function Framework\Polyfill\array_key_last;
use function Framework\Polyfill\is_iterable;

if (!function_exists('Framework\app')) {
    /**
     * Get the container instance.
     *
     * @template TClass
     *
     * @param string|class-string<TClass>|null $abstract
     * @param array $parameters
     *
     * @return ($abstract is class-string<TClass> ? TClass : ($abstract is null ? Application : mixed))
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Application::get_instance();
        }

        return Application::get_instance()->make($abstract, $parameters);
    }
}

if (!function_exists('Framework\deep_get')) {
    /**
     * Get a value from an array using a dot notation key.
     *
     * @param   array $target         The target array to get the value from.
     * @param   string|array $key     The key to get the value from.
     * @param   mixed $default        The default value to return if the key is not found.
     * 
     * @return  mixed                 The value from the array or the default value if the key is not found.
     * 
     * @since 1.0.0
     */
    function deep_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $index => $segment) {
            unset($key[$index]);

            if (is_null($segment)) {
                return $target;
            }

            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_iterable($target)) {
                    return $default;
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = deep_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            switch ($segment) {
                case '\*':
                    $segment = '*';
                    break;
                case '\{first}':
                    $segment = '{first}';
                    break;
                case '{first}':
                    $segment = array_key_first(Arr::from($target));
                    break;
                case '\{last}':
                    $segment = '{last}';
                    break;
                case '{last}':
                    $segment = array_key_last(Arr::from($target));
                    break;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }
}


if (!function_exists('Framework\config')) {
    /**
     * Get the config
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        static $cache = [];

        $filename = strpos($key, '.') ? substr($key, 0, strpos($key, '.')) : $key;
        $key = strpos($key, '.') ? substr($key, strpos($key, '.') + 1) : null;

        if (!isset($cache[$filename])) {
            $path = app()->config_path("{$filename}.php");

            if (file_exists($path)) {
                $cache[$filename] = include $path;
            } else {
                $cache[$filename] = null;
            }
        }

        if (is_null($cache[$filename])) {
            return $default;
        }

        return deep_get($cache[$filename], $key, $default);
    }
}

if (!function_exists('Framework\user')) {
    /**
     * Get the user instance.
     *
     * @return User
     */
    function user($user_id = null)
    {
        return app()->make(User::class, ['user_id' => $user_id]);
    }
}

if (!function_exists('Framework\response')) {
    /**
     * Get the response instance.
     *
     * @return Response
     */
    function response()
    {
        return app()->make(Response::class)->with_headers([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'no-referrer-when-downgrade',
            'Cache-Control' => 'public, max-age=60, stale-while-revalidate=30',
        ]);
    }
}

if (!function_exists('Framework\request')) {
    /**
     * Get the request instance.
     *
     * @param string|null $key
     * @param mixed $default
     * 
     * @return ($key is null ? Request : ($key is array ? array : mixed))
     * 
     * @since 1.0.0
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        $value = app('request')->get($key, $default);

        return is_null($value) ? value($default) : $value;
    }
}

if (!function_exists('Framework\with_prefix')) {
    /**
     * Get the key with prefix applied.
     *
     * @param string $key
     * @return string
     */
    function with_prefix(string $key)
    {
        $prefix = app()->prefix();

        if (Str::starts_with($key, $prefix)) {
            return $key;
        }

        return $prefix . $key;
    }
}

if (!function_exists('Framework\without_prefix')) {
    /**
     * Get the key without prefix applied.
     *
     * @param string $key
     * @return string
     */
    function without_prefix(string $key)
    {
        $prefix = app()->prefix();

        if (!Str::starts_with($key, $prefix)) {
            return $key;
        }

        return substr($key, strlen($prefix));
    }
}


if (!function_exists('Framework\redirect')) {
    /**
     * Redirect to the given location.
     */
    function redirect($location)
    {
        Url::redirect($location);
    }
}

if (!function_exists('Framework\is_valid_json')) {
    /**
     * Check if the string is a valid JSON.
     * 
     * @param string $string
     * @return bool
     */
    function is_valid_json($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);

        return (json_last_error() === JSON_ERROR_NONE);
    }
}

if (!function_exists('Framework\clean_path')) {
    /**
     * Clean and normalize file paths for consistency.
     *
     * @param string $path
     * @param bool   $trailing_slash Add a trailing slash? Default true.
     * @return string
     */
    function clean_path(string $path, bool $trailing_slash = true)
    {
        $path = wp_normalize_path($path);

        return $trailing_slash
            ? trailingslashit($path)
            : untrailingslashit($path);
    }
}

if (!function_exists('Framework\uuid')) {
    /**
     * Generate a UUID.
     * 
     * @return string
     */
    function uuid()
    {
        return Utils::uuid();
    }
}

if (!function_exists('Framework\url')) {
    /**
     * Generate a URL.
     * 
     * @param string $url
     * @param array $query_vars
     * @return string
     */
    function url($url, $query_vars = [])
    {
        return Url::make($url, $query_vars);
    }
}

if (!function_exists('Framework\is_block_theme')) {
    /**
     * Check if the site is using a block template
     *
     * This function will return true if the site is using a block template and false otherwise.
     *
     * @return bool True if the site is using a block template, false otherwise.
     */
    function is_block_theme()
    {
        return function_exists('wp_is_block_theme') && wp_is_block_theme();
    }
}

if (!function_exists('Framework\migrator')) {
    /**
     * Get the migrator instance.
     *
     * @return Migrator
     */
    function migrator()
    {
        return app()->make(Migrator::class);
    }
}

if (!function_exists('Framework\tap')) {
    /**
     * Call the given Closure with the given value.
     *
     * @param  mixed  $value
     * @param  \Closure  $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if (!function_exists('Framework\faker')) {
    /**
     * Get the fake instance.
     *
     * @return \Faker\Generator
     */
    function faker()
    {
        return app()->make(\Faker\Factory::class);
    }
}

if (!function_exists('Framework\dd')) {
    /**
     * Dump and die
     * 
     * @param mixed ...$args
     * @return never
     */
    function dd(...$args)
    {
        echo '<xmp>';
        foreach ($args as $arg) {
            echo "\n";
            var_dump($arg);
            echo "\n";
        }
        echo '</xmp>';
        die();
    }
}

if (!function_exists('Framework\pr')) {
    /**
     * print and die
     * 
     * @param mixed ...$args
     * @return never
     */
    function pr(...$args)
    {
        echo '<xmp>';
        foreach ($args as $arg) {
            echo "\n";
            print_r($arg);
            echo "\n";
        }
        echo '</xmp>';
        die();
    }
}

if (!function_exists('Framework\app_path')) {
    /**
     * Get the path to the application directory.
     *
     * @param string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app()->path($path);
    }
}

if (!function_exists('Framework\config_path')) {
    /**
     * Get the path to the config directory.
     *
     * @param string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->config_path($path);
    }
}

if (!function_exists('Framework\database_path')) {
    /**
     * Get the path to the database directory.
     *
     * @param string $path
     * @return string
     */
    function database_path($path = '')
    {
        return app()->database_path($path);
    }
}

if (!function_exists('Framework\base_path')) {
    /**
     * Get the path to the base directory.
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->base_path($path);
    }
}

if (!function_exists('Framework\resource_path')) {
    /**
     * Get the path to the resources directory.
     *
     * @param string $path
     * @return string
     */
    function resource_path($path = '')
    {
        return app()->resource_path($path);
    }
}

if (!function_exists('Framework\bootstrap_path')) {
    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path
     * @return string
     */
    function bootstrap_path($path = '')
    {
        return app()->bootstrap_path($path);
    }
}

if (!function_exists('Framework\collection')) {
    /**
     * Create a collection instance from an array.
     *
     * @param array $array
     * @return Collection
     */
    function collection(array $array = [])
    {
        return new Collection($array);
    }
}

if (!function_exists('Framework\resource_url')) {
    /**
     * Get the path to the resources directory.
     *
     * @param string $path
     * @return string
     */
    function resource_url($path = '')
    {
        return app()->base_url(path_join('resources', $path));
    }
}

if (!function_exists('Framework\json_decoded_data')) {
    /**
     * Get the decoded JSON data from a file.
     * 
     * @param string $file_path
     * @param bool $associative
     * @return mixed
     */
    function json_decoded_data(string $file_path, bool $associative = true)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        $content = file_get_contents($file_path);

        return json_decode($content, $associative);
    }
}

if (!function_exists('Framework\value')) {
    /**
     * Get the value of a variable.
     * 
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    function value($value, ...$args) {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}
