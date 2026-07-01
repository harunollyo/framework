<?php

namespace Example\App\Listeners;

use Example\App\Events\SampleEvent;
use Framework\Listener;

use function Framework\dd;

class SampleListener extends Listener
{
    public function handle(SampleEvent $event)
    {
        file_put_contents(__DIR__ . '/test.log', 'from-sample-listener: ' . $event->blog->id . PHP_EOL, FILE_APPEND);
    }

    public function priority()
    {
        return 2;
    }
}