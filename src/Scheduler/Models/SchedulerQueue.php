<?php

namespace Framework\Scheduler\Models;

use Framework\Database\Query\Model;

class SchedulerQueue extends Model
{
    protected $table = 'kirki_scheduler_jobs';
    protected $primary_key = 'id';

    protected $fillable = [
        'resolver',
        'args',
        'status',
        'priority',
        'scheduled_at',
        'claim_id',
        'attempts',
        'created_at',
        'updated_at',
    ];
}
