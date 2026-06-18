<?php
/**
 * Extends the Action contract with recurrence control for scheduled tasks.
 * Adds should_stop and get_additional_args for recurring WordPress cron or scheduler jobs.
 * Lets actions self-manage their repeat lifecycle.
 *
 * @package    Framework
 * @subpackage Contracts
 * @since      1.0.0
 */
namespace Framework\Contracts;

defined('ABSPATH') || exit;

interface RecurrableScheduler extends Action
{
    /**
     * Should stop.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function should_stop();

    /**
     * Get the additional args.
     *
     * @return array|false
     *
     * @since 1.0.0
     */
    public function get_additional_args();
}
