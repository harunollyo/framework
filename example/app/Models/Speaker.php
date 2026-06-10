<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Speaker extends Model
{
    protected $table = 'framework_speakers';

    protected $primary_key = 'id';

    protected $casts = [];

    protected $fillable = [
        'name',
        'designation',
        'email',
        'website',
        'created_at',
        'updated_at',
    ];

    public function events()
    {
        return $this->belongs_to_many(Event::class, 'framework_event_speaker');
    }
}
