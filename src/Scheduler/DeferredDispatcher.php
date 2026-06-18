<?php
/**
 * Fluent builder wrapping a queueable job before it is persisted to the queue.
 * Supports chaining delay, priority, and batch size before calling dispatch to enqueue.
 * Mirrors delayed dispatch pattern for the WordPress scheduler.
 *
 * @package    Framework
 * @subpackage Scheduler
 * @since      1.0.0
 */
namespace Framework\Scheduler;

defined('ABSPATH') || exit;

class DeferredDispatcher
{
    /**
     * The job instance that is being deferred for dispatching.
     *
     * @var mixed
     *
     * @since 1.0.0
     */
    protected $job;

    /**
     * Initialize the dispatcher with a specific job instance.
     *
     * @param mixed $job The job.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * Specify the delay (time or interval) before the job should be executed.
     *
     * @param mixed $moment The moment.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function delay($moment)
    {
        $this->job->delay($moment);

        return $this;
    }

    /**
     * Set the execution priority level for the job.
     *
     * @param int $priority The priority.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function priority(int $priority)
    {
        $this->job->priority($priority);

        return $this;
    }

    /**
     * Ensure the job is executed without any scheduled delay.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function without_delay()
    {
        $this->job->delay(null);

        return $this;
    }

    /**
     * Proxy method calls to the underlying job instance to allow fluent configuration.
     *
     * @param mixed $method The method name.
     * @param mixed $parameters The parameters array.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function __call($method, $parameters)
    {
        $this->job->$method(...$parameters);

        return $this;
    }

    /**
     * Finalize the job by storing it in the database and triggering it immediately
     * via an async worker if no delay is specified.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __destruct()
    {
        $this->job->store();

        // If no delay then trigger the job immediately using async worker.
        if ($this->job->get_delay() === null) {
            $this->job->trigger_async_worker();
        }
    }
}
