<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Blog extends Model
{
    protected $table = 'framework_blogs';

    protected $primary_key = 'id';

    protected $casts = [
        'status' => 'bool',
        'published_at' => 'datetime',
    ];

    // protected $fillable = [
    //     'user_id',
    //     'category_id',
    //     'title',
    //     'slug',
    //     'excerpt',
    //     'body',
    //     'featured_image',
    //     'status',
    //     'published_at',
    //     'created_at',
    //     'updated_at',
    // ];
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongs_to(Category::class);
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
        return $this->has_many(Comment::class);
    }
}
