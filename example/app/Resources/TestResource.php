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
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'content' => $this->resource->content,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'other' => $this->other,
        ];
    }
}