<?php

namespace Framework\Contracts;

interface ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @param array $args
     * @return void
     */
    public function register(...$args);
}
