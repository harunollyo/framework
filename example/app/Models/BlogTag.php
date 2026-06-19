<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class BlogTag extends Model
{
    protected $table = 'framework_blog_tag';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
        'blog_id' => 'integer',
        'tag_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'blog_id',
        'tag_id',
        'created_at',
        'updated_at',
    ];

    public function blog()
    {
        return $this->belongs_to(Blog::class);
    }

    public function tag()
    {
        return $this->belongs_to(Tag::class);
    }
}
