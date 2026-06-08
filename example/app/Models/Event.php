<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Event extends Model
{
    protected $table = 'framework_events';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
    ];

    protected $fillable = [
        'name',
        'description',
        'date',
        'time',
        'location',
        'organizer',
        'created_at',
        'updated_at',
    ];
}
