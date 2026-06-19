<?php

namespace Example\App\Models;

use Example\App\Casts\AsSerialize;
use Framework\Database\Query\Model;

class Event extends Model
{
    protected $table = 'framework_events';

    protected $primary_key = 'id';

    protected $casts = [
        'description' => AsSerialize::class,
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

    public function speakers()
    {
        return $this->belongs_to_many(Speaker::class, 'framework_event_speaker');
    }

    public function abc()
    {
        return $this->belongs_to_many(Speaker::class, 'framework_event_speaker');
    }
}
