<?php
/**
 * Marker interface identifying classes as queueable background jobs.
 * No methods; presence signals the scheduler to persist and defer execution.
 * Implemented by job classes using the Queueable trait.
 *
 * @package    Framework
 * @subpackage Scheduler\Contracts
 * @since      1.0.0
 */
namespace Framework\Scheduler\Contracts;

defined('ABSPATH') || exit;

interface ShouldQueue
{
    // An interface to identify a scheduler queue job.
}
