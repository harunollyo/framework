<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Comment extends Model
{
    protected $table = 'framework_comments';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
        'blog_id' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'is_approved' => 'boolean',
    ];

    protected $fillable = [
        'blog_id',
        'user_id',
        'parent_id',
        'body',
        'is_approved',
        'created_at',
        'updated_at',
    ];

    public function blog()
    {
        return $this->belongs_to(Blog::class);
    }

    public function parent()
    {
        return $this->belongs_to(static::class, 'parent_id');
    }

    public function replies()
    {
        return $this->has_many(Reply::class, 'comment_id');
    }
}
