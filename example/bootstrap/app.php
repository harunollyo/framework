<?php

use Framework\Application;

return Application::configure(FRAMEWORK_EXAMPLE_PATH)
    ->use_routing(FRAMEWORK_EXAMPLE_PATH . 'routes/api.php')
    ->use_prefix(FRAMEWORK_EXAMPLE_PREFIX)
    ->use_app_mode('development')
    ->use_routing(FRAMEWORK_EXAMPLE_PATH . 'routes/site.php')
    ->use_view_path(FRAMEWORK_EXAMPLE_PATH . 'resources/views')
    ->boot();
