<?php

namespace Example\App\Resources;

use Framework\Resource;

class TestResource extends Resource
{
    protected $other;

    public function __construct($resource, $other)
    {
        parent::__construct($resource);
        $this->other = $other;
    }

    public function to_array()
    {
        return array_merge((array) $this->resource, $this->other);
    }
}