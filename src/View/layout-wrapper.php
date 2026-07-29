<?php
/**
 * Layout wrapper for template_include views that use theme header/footer.
 *
 * WordPress includes this file via template_include. The active ViewContext
 * holds the real view path and data; this stub renders content and wraps it.
 *
 * @package    Framework
 * @subpackage View
 * @since      2.1.2
 */

defined('ABSPATH') || exit;

use Framework\View\TemplateEngine;
use Framework\View\ViewContext;

use function Framework\app;

$context = app(ViewContext::class);
$active = $context->get_active();

if ($active === null || empty($active['resolved_path'])) {
    return;
}

$path = $active['resolved_path'];

ob_start();
require $path;
$content = (string) ob_get_clean();

echo app(TemplateEngine::class)->wrap_layout($content);
