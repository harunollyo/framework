<?php
/**
 * Eloquent model for the themeum_framework_scheduler_jobs database table.
 * Tracks resolver, args, status, priority, scheduling time, claim ID, and attempt count.
 * The persistence layer for background job records.
 *
 * @package    Framework
 * @subpackage Scheduler\Models
 * @since      1.0.0
 */
namespace Framework\Scheduler\Models;

defined('ABSPATH') || exit;

use Framework\Database\Query\Model;

class SchedulerQueue extends Model
{
    protected $table = 'themeum_framework_scheduler_jobs';
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
