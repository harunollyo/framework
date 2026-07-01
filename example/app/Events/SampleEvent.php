<?php

namespace Example\App\Events;

use Framework\Concerns\Dispatchable;

class SampleEvent
{
    use Dispatchable;

    /**
     * The blog.
     *
     * @var \Example\App\Models\Blog
     *
     * @since 1.0.0
     */
    public $blog;

    /**
     * Create a new sample event instance.
     *
     * @param \Example\App\Models\Blog $blog The blog.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct($blog)
    {
        $this->blog = $blog;
    }
}