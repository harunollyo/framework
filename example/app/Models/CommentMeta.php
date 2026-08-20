<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class CommentMeta extends Model
{
    protected $table = 'framework_comment_meta';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
    ];
}
