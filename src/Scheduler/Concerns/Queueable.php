<?php
/**
 * Combines Dispatchable and HasAsyncWorker with job configuration for priority, delay, batch size, and arguments.
 * Base trait for classes that implement ShouldQueue and run asynchronously.
 * Defines the fluent API consumed by DeferredDispatcher.
 *
 * @package    Framework
 * @subpackage Scheduler\Concerns
 * @since      1.0.0
 */
namespace Framework\Scheduler\Concerns;

defined('ABSPATH') || exit;

use Framework\Scheduler\Constants\Config;

trait Queueable
{
    use Dispatchable;
    use HasAsyncWorker;

    /**
     * The arguments that will be passed to the job's handle method.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $args = [];

    /**
     * The priority of the job. Lower numbers indicate higher priority.
     *
     * @var int
     *
     * @since 1.0.0
     */
    protected $priority = 10;

    /**
     * The delay in seconds before the job should be processed.
     *
     * @var int|null
     *
     * @since 1.0.0
     */
    protected $delay = null;

    /**
     * The number of jobs to process in a single batch.
     *
     * @var int
     *
     * @since 1.0.0
     */
    protected $batch = Config::DEFAULT_BATCH_SIZE;

    /**
     * Get the class name of the job resolver.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function get_resolver()
    {
        return get_class($this);
    }

    /**
     * Set the arguments for the job.
     *
     * @param mixed $values The values.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function args($values = [])
    {
        $values = is_array($values) ? $values : func_get_args();
        $this->args = $values;

        return $this;
    }

    /**
     * Get the arguments assigned to the job.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function get_args()
    {
        return $this->args;
    }

    /**
     * Set the job priority.
     *
     * @param int $priority The priority.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function priority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get the job priority, clamped between 0 and 255.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function get_priority()
    {
        return max(0, min($this->priority, 255));
    }

    /**
     * Set the delay for the job execution.
     *
     * @param mixed $moment The moment.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function delay($moment = null)
    {
        $this->delay = $moment;

        return $this;
    }

    /**
     * Get the delay before the job is executed.
     *
     * @return int|null
     *
     * @since 1.0.0
     */
    public function get_delay()
    {
        return $this->delay;
    }

    /**
     * Set the number of items per batch.
     *
     * @param int $size The size.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function batch(int $size)
    {
        $this->batch = $size;

        return $this;
    }

    /**
     * Get the batch size.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function get_batch()
    {
        return $this->batch;
    }

    /**
     * Set the number of times the job should be retried on failure.
     *
     * @param int $attempts The attempts.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function retry(int $attempts)
    {
        $this->retry = $attempts;

        return $this;
    }

    /**
     * Get the number of times the job should be retried on failure.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function get_retry()
    {
        return intval($this->retry ?? 0);
    }
}
