<?php

use Themeum\Framework\Application;

Application::macro('is_dev_mode', function () {
    return defined('WP_DEBUG') && WP_DEBUG;
});

return Application::configure(FRAMEWORK_EXAMPLE_PATH)
    ->use_routing(FRAMEWORK_EXAMPLE_PATH . 'routes/api.php')
    ->use_prefix(FRAMEWORK_EXAMPLE_PREFIX)
    ->boot();
