<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Tag extends Model
{
    protected $table = 'framework_tags';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
    ];

    protected $fillable = [
        'name',
        'slug',
        'created_at',
        'updated_at',
    ];

    public function blogs()
    {
        return $this->belongs_to_many(Blog::class, 'framework_blog_tag');
    }
}
