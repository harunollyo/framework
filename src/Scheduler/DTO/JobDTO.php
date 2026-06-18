<?php
/**
 * DTO carrying id, args, and resolver class name for a queued job record.
 * Passed from the repository to Runner when resolving and executing jobs.
 * Thin data bag decoupling persistence from job execution logic.
 *
 * @package    Framework
 * @subpackage Scheduler\DTO
 * @since      1.0.0
 */
namespace Framework\Scheduler\DTO;

defined('ABSPATH') || exit;

use Framework\DTO;

class JobDTO extends DTO
{
    /**
     * The unique identifier for the job.
     *
     * @var int
     *
     * @since 1.0.0
     */
    public int $id;

    /**
     * The arguments to be passed to the job handler.
     *
     * @var array
     *
     * @since 1.0.0
     */
    public array $args;

    /**
     * The resolver class or method for the job.
     *
     * @var string
     *
     * @since 1.0.0
     */
    public string $resolver;
}
