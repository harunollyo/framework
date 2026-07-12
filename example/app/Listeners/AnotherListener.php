<?php

namespace Example\App\Listeners;

use Example\App\Events\SampleEvent;
use Framework\Listener;

class AnotherListener extends Listener
{
    public function handle(SampleEvent $event)
    {
        return true;
    }

    public function priority()
    {
        return 1;
    }
}