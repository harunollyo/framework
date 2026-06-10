<?php

namespace Framework\Wordpress\Models;

use Framework\Database\Query\Model;

class Option extends Model
{
    protected $timestamps = false;

    protected $table = 'options';

    protected $primary_key = 'option_id';

    protected $casts = [
        'option_id' => 'integer',
        'option_name' => 'string',
        'option_value' => 'unserialize',
    ];

    protected $fillable = [
        'option_name',
        'option_value',
        'autoload',
    ];
}