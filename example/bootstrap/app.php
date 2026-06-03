<?php

use Themeum\Framework\Application;

return Application::configure(FRAMEWORK_EXAMPLE_PATH)
    ->use_routing(FRAMEWORK_EXAMPLE_PATH . 'routes/api.php')
    ->use_prefix(FRAMEWORK_EXAMPLE_PREFIX)
    ->app_mode('development')
    ->boot();
