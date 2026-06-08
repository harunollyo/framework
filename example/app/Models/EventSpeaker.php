<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class EventSpeaker extends Model
{
    protected $table = 'framework_event_speaker';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
        'event_id' => 'integer',
        'speaker_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'event_id',
        'speaker_id',
        'created_at',
        'updated_at',
    ];
}
