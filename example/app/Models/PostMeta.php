<?php

namespace Example\App\Models;

use Example\App\Casts\AsSerialize;
use Framework\Database\Query\Model;

class PostMeta extends Model
{
    protected $timestamps = false;
    protected $table = 'postmeta';

    protected $primary_key = 'meta_id';

    protected $casts = [
      'meta_value' => AsSerialize::class,
    ];

    protected $fillable = [
      'post_id',
      'meta_key',
      'meta_value',
    ];
}
