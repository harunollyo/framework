<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $primary_key = 'ID';

    protected $casts = [
        'ID' => 'integer',
    ];

    protected $fillable = [
      // Add your fillable fields here
    ];
}
