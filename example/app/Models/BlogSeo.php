<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class BlogSeo extends Model
{
    protected $table = 'framework_blog_seo';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
        'blog_id' => 'integer',
    ];

    protected $fillable = [
        'blog_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'created_at',
        'updated_at',
    ];

    public function blog()
    {
        return $this->belongs_to(Blog::class);
    }
}
