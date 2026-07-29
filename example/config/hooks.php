<?php

use Framework\Wordpress\Hooks\Actions\RegisterRestApi;
use Framework\Wordpress\Hooks\Actions\RegisterSiteRoutes;

return [
    'actions' => [
        RegisterRestApi::class,
        RegisterSiteRoutes::class,
    ],
    'filters' => [],
];
