<?php
/**
 * Template engine for rendering site route views with theme layout support.
 * Resolves views from the theme first, then the application view path.
 *
 * @package    Framework
 * @subpackage View
 * @since      1.0.0
 */
namespace Framework\View;

defined('ABSPATH') || exit;

use RuntimeException;

use function Framework\app;

class TemplateEngine
{
    /**
     * Shared data available to all views.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $shared = [];

    /**
     * Cached block theme header markup.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected $block_header = '';

    /**
     * Cached block theme footer markup.
     *
     * @var string
     *
     * @since 1.0.0
     */
    protected $block_footer = '';

    /**
     * Share a value with all views.
     *
     * @param string $key The data key.
     * @param mixed $value The data value.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function share(string $key, $value)
    {
        $this->shared[$key] = $value;
    }

    /**
     * Get data shared with all views.
     *
     * @return array
     *
     * @since 2.1.2
     */
    public function get_shared()
    {
        return $this->shared;
    }

    /**
     * Render a view template to a string.
     *
     * @param string $view The view name in dot notation.
     * @param array $data The data to pass to the view.
     * @param bool $layout Whether to wrap with theme header/footer.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render(string $view, array $data = [], bool $layout = true)
    {
        $path = $this->resolve_path($view);

        if ($path === '') {
            throw new RuntimeException(sprintf('View [%s] not found.', $view));
        }

        $data = array_merge($this->shared, $data);

        $content = $this->render_file($path, $data);

        if (!$layout) {
            return $content;
        }

        return $this->wrap_layout($content);
    }

    /**
     * Resolve a view name to an absolute file path.
     *
     * @param string $view The view name in dot notation.
     *
     * @return string Absolute path or empty string when not found.
     *
     * @since 1.0.0
     */
    public function resolve_path(string $view)
    {
        $relative = $this->normalize_template_name($view) . '.php';

        if (function_exists('locate_template')) {
            $theme_path = locate_template('views/' . $relative);

            if ($theme_path !== '') {
                return apply_filters('framework/view/path', $theme_path, $view);
            }
        }

        $base_path = app()->view_path();
        $candidate = rtrim($base_path, '/\\') . '/' . $relative;
        $resolved = realpath($candidate);
        $base_real = realpath($base_path);

        if ($resolved === false || $base_real === false || strpos($resolved, $base_real) !== 0) {
            return apply_filters('framework/view/path', '', $view);
        }

        if (!is_file($resolved) || !is_readable($resolved)) {
            return apply_filters('framework/view/path', '', $view);
        }

        return apply_filters('framework/view/path', $resolved, $view);
    }

    /**
     * Normalize a template name into a relative path without extension.
     *
     * @param string $template Template name or relative path.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function normalize_template_name(string $template)
    {
        $template = preg_replace('/\.php$/i', '', trim($template));

        return str_replace('.', '/', $template);
    }

    /**
     * Check if the current theme is a block theme.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function is_block_theme()
    {
        return function_exists('wp_is_block_theme') && wp_is_block_theme();
    }

    /**
     * Render a PHP template file with extracted data.
     *
     * @param string $path Absolute template path.
     * @param array $data Template data.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function render_file(string $path, array $data)
    {
        ob_start();

        // phpcs:ignore Framework.NamingConventions.SnakeCaseVariable.NotSnakeCase
        (static function ($__path, $__data) {
            // phpcs:ignore Framework.NamingConventions.SnakeCaseVariable.NotSnakeCase
            extract($__data, EXTR_SKIP);
            // phpcs:ignore Framework.NamingConventions.SnakeCaseVariable.NotSnakeCase
            require $__path;
        })($path, $data);

        return (string) ob_get_clean();
    }

    /**
     * Wrap rendered content with theme header and footer.
     *
     * @param string $content The view content.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function wrap_layout(string $content)
    {
        ob_start();

        $this->render_header();
        echo $content;
        $this->render_footer();

        return (string) ob_get_clean();
    }

    /**
     * Render the theme header for classic or block themes.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function render_header()
    {
        if ($this->is_block_theme()) {
            $this->block_header = do_blocks(
                sprintf(
                    '<!-- wp:template-part {"slug":"header","theme":"%s","tagName":"header","layout":{"inherit":true}} /-->',
                    esc_attr(get_stylesheet())
                )
            );
            $this->block_footer = do_blocks(
                sprintf(
                    '<!-- wp:template-part {"slug":"footer","theme":"%s","tagName":"footer","className":"site-footer","layout":{"inherit":true}} /-->',
                    esc_attr(get_stylesheet())
                )
            );
            ?>
            <!doctype html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo('charset'); ?>">
                <?php wp_head(); ?>
            </head>
            <body <?php body_class(); ?>>
                <?php wp_body_open(); ?>
                <div class="wp-site-blocks">
                <?php
                echo $this->block_header;
                return;
        }

        get_header();
    }

    /**
     * Render the theme footer for classic or block themes.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function render_footer()
    {
        if ($this->is_block_theme()) {
            echo $this->block_footer;
            echo '</div>';
            wp_footer();
            echo '</body>';
            echo '</html>';

            return;
        }

        get_footer();
    }

    /**
     * Include a view template file directly (without capturing output).
     *
     * @param string $view The view name in dot notation.
     * @param array $data The data to pass to the view.
     * @param bool $once Whether to load the template only once.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function include(string $view, array $data = [], bool $once = true)
    {
        $path = $this->resolve_path($view);

        if ($path === '') {
            return;
        }

        $data = array_merge($this->shared, $data);

        extract($data, EXTR_SKIP);

        if ($once) {
            require_once $path;

            return;
        }

        require $path;
    }
}