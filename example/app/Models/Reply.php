<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Reply extends Model
{
    protected $table = 'framework_replies';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
        'comment_id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $fillable = [
        'comment_id',
        'user_id',
        'body',
        'created_at',
        'updated_at',
    ];

    public function comment()
    {
        return $this->belongs_to(Comment::class);
    }
}
