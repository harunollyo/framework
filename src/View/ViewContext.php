<?php
/**
 * Active view data context for template_include site routes.
 *
 * Registers controller view data and exposes it to the matched template via
 * view_data(), with caller verification to prevent hijacking.
 *
 * @package    Framework
 * @subpackage View
 * @since      2.1.2
 */
namespace Framework\View;

defined('ABSPATH') || exit;

use Framework\Supports\Arr;

use function Framework\app;

class ViewContext
{
    /**
     * Active context for the current request.
     *
     * @var array|null
     *
     * @since 2.1.2
     */
    protected $active = null;

    /**
     * Whether the shutdown clear hook was registered.
     *
     * @var bool
     *
     * @since 2.1.2
     */
    protected $shutdown_registered = false;

    /**
     * Prepare and register view data for a template_include dispatch.
     *
     * @param View $view Controller view return value.
     * @param string $route_name Named route or empty string.
     * @param string $resolved_path Absolute template filesystem path.
     *
     * @return array Final data stored in the active context.
     *
     * @since 2.1.2
     */
    public function prepare(
        View $view,
        string $route_name,
        string $resolved_path
    ) {
        $engine = app(TemplateEngine::class);
        $template = $view->get_template();
        $data = array_merge($engine->get_shared(), $view->get_data());

        $this->activate([
            'template' => $template,
            'route_name' => $route_name,
            'resolved_path' => $this->normalize_path($resolved_path),
            'data' => $data,
        ]);

        return $data;
    }

    /**
     * Read a value from the active context when the caller is authorized.
     *
     * Supports dot notation for nested keys (e.g. `product.name`).
     *
     * @param string|null $key Data key, or null for the full array.
     * @param mixed $default Default when missing or unauthorized.
     *
     * @return mixed
     *
     * @since 2.1.2
     */
    public function get($key = null, $default = null)
    {
        if ($this->active === null || !$this->caller_is_authorized()) {
            return $key === null ? [] : $default;
        }

        if ($key === null) {
            return $this->active['data'];
        }

        return Arr::get($this->active['data'], $key, $default);
    }

    /**
     * Clear the active context.
     *
     * @return void
     *
     * @since 2.1.2
     */
    public function clear()
    {
        $this->active = null;
    }

    /**
     * Get the active context metadata, or null when none.
     *
     * @return array|null
     *
     * @since 2.1.2
     */
    public function get_active()
    {
        return $this->active;
    }

    /**
     * Set the active context and ensure it clears on shutdown.
     *
     * @param array $context Context payload.
     *
     * @return void
     *
     * @since 2.1.2
     */
    protected function activate(array $context)
    {
        $this->active = $context;
        $this->ensure_shutdown_registered();
    }

    /**
     * Register a shutdown callback to clear the active context once.
     *
     * @return void
     *
     * @since 2.1.2
     */
    protected function ensure_shutdown_registered()
    {
        if ($this->shutdown_registered) {
            return;
        }

        $this->shutdown_registered = true;

        add_action('shutdown', function () {
            $this->clear();
        }, 999);
    }

    /**
     * Whether the current call stack originates from the active template.
     *
     * @return bool
     *
     * @since 2.1.2
     */
    protected function caller_is_authorized()
    {
        if ($this->active === null || empty($this->active['resolved_path'])) {
            return false;
        }

        $resolved = $this->active['resolved_path'];
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($trace as $frame) {
            if (empty($frame['file'])) {
                continue;
            }

            $file = $this->normalize_path($frame['file']);

            if ($file === $resolved) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize a filesystem path for comparison.
     *
     * @param string $path Absolute path.
     *
     * @return string
     *
     * @since 2.1.2
     */
    protected function normalize_path(string $path)
    {
        $real = realpath($path);

        if ($real !== false) {
            return $real;
        }

        return str_replace('\\', '/', $path);
    }
}
