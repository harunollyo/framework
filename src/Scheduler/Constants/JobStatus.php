<?php
/**
 * Defines the four queue job lifecycle states: pending, processing, failed, and completed.
 * Used by QueueRepository and Runner to filter and transition job records.
 * Keeps status values consistent across the scheduler database table.
 *
 * @package    Framework
 * @subpackage Scheduler\Constants
 * @since      1.0.0
 */
namespace Framework\Scheduler\Constants;

defined('ABSPATH') || exit;

class JobStatus
{
    /**
     * The job is pending execution.
     */
    public const PENDING = 'pending';

    /**
     * The job is currently being processed.
     */
    public const PROCESSING = 'processing';

    /**
     * The job has failed to complete.
     */
    public const FAILED = 'failed';

    /**
     * The job has completed successfully.
     */
    public const COMPLETED = 'completed';
}
