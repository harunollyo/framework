<?php

namespace Example\App\Listeners;

use Example\App\Events\SampleEvent;
use Framework\Listener;

use function Framework\dd;

class SampleListener extends Listener
{
    public function handle(SampleEvent $event)
    {
        return true;
    }

    public function priority()
    {
        return 2;
    }
}