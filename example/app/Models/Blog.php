<?php

namespace Example\App\Models;

use Example\App\Casts\AsSerialize;
use Framework\Database\Query\Model;

class Blog extends Model
{
    protected $table = 'framework_blogs';

    protected $primary_key = 'id';

    protected $casts = [
        'status' => 'string',
        'published_at' => 'datetime',
        'body' => AsSerialize::class,
    ];

    protected $guarded = ['id'];

    public function category()
    {
        return $this->has_one(Category::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongs_to_many(Tag::class, 'framework_blog_tag');
    }

    public function seo()
    {
        return $this->has_one(BlogSeo::class);
    }

    public function comments()
    {
        return $this->has_many(Comment::class, 'blog_id', 'id');
    }
}
