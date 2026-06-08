<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Speaker extends Model
{
    protected $table = 'framework_speakers';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
    ];

    protected $fillable = [
        'name',
        'designation',
        'email',
        'website',
        'created_at',
        'updated_at',
    ];
}
