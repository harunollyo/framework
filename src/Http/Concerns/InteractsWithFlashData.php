<?php
/**
 * Trait for redirect responses to attach flash data, old input, and errors fluently.
 * Attached values are written into the session store rather than held on the response.
 * The session is persisted at the response's own send point, before cookies are flushed.
 *
 * @package    Framework
 * @subpackage Http\Concerns
 * @since      1.0.0
 */
namespace Framework\Http\Concerns;

defined('ABSPATH') || exit;

use Framework\Managers\SessionManager;

use function Framework\app;

trait InteractsWithFlashData
{
    /**
     * Flash one value, or many values, for the next request.
     *
     * @param string|array $key The key, or an array of key and value pairs.
     * @param mixed $value The value when a single key is given.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function with($key, $value = null)
    {
        $values = is_array($key) ? $key : [$key => $value];

        foreach ($values as $name => $item) {
            $this->session_manager()->flash((string) $name, $item);
        }

        return $this;
    }

    /**
     * Flash the given input, or the current request's input, as old input.
     *
     * Sensitive fields and uploaded files are never written; see the session's
     * never-flash list. Passing an array bypasses the request but is still
     * filtered, so a caller cannot flash a password by mistake.
     *
     * @param array|null $input The input to flash, or null for the current request's input.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function with_input(?array $input = null)
    {
        $session = $this->session_manager();

        if (is_null($input)) {
            $input = app('request')->flashable_input();
        } else {
            $input = array_diff_key($input, array_flip($session->get_never_flash()));
        }

        $session->flash_input($input);

        return $this;
    }

    /**
     * Flash validation errors for the next request.
     *
     * Errors are stored as plain arrays so no framework object ever enters the
     * storage driver.
     *
     * @param array|\Framework\Supports\ErrorBag $errors The errors keyed by field name.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function with_errors($errors)
    {
        if ($errors instanceof \Framework\Supports\ErrorBag) {
            $errors = $errors->all();
        }

        $normalized = [];

        foreach ((array) $errors as $field => $messages) {
            $normalized[(string) $field] = array_values(array_map('strval', (array) $messages));
        }

        $this->session_manager()->flash('errors', $normalized);

        return $this;
    }

    /**
     * Get the session manager instance.
     *
     * @return SessionManager
     *
     * @since 1.0.0
     */
    protected function session_manager()
    {
        return app(SessionManager::class);
    }
}
