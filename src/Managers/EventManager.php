<?php
/**
 * Dispatches domain events to registered listener classes loaded from cache.
 * Resolves listeners from the cached map, sorts by priority, and invokes handle on each match.
 * Core of the framework event-driven architecture.
 *
 * @package    Framework
 * @subpackage Managers
 * @since      1.0.0
 */
namespace Framework\Managers;

defined('ABSPATH') || exit;

use Closure;
use Framework\Listener;
use InvalidArgumentException;

use function Framework\config_path;

class EventManager
{
    /**
     * The listeners array.
     *
     * @var array
     */
    protected array $listeners = [];

    /**
     * Create a new event manager instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->load_listeners();
    }

    /**
     * Load the listeners from the cache.
     *
     * @return $this
     */
    protected function load_listeners()
    {
        $listeners_cache_path = config_path('listeners.cache.php');

        if (!file_exists($listeners_cache_path)) {
            return $this;
        }

        $this->listeners = require $listeners_cache_path;

        return $this;
    }

    /**
     * Dispatch the event.
     *
     * @param  mixed $event
     * @return void
     */
    public function dispatch($event)
    {
        if (!is_object($event)) {
            throw new InvalidArgumentException(sprintf(
                'The event must be an object, got [%s]',
                gettype($event)
            ));
        }

        $event_class = get_class($event);

        if (!isset($this->listeners[$event_class])) {
            return;
        }

        $event_listeners = $this->listeners[$event_class] ?? [];

        foreach ($event_listeners as $listener) {
            $this->resolve($listener, $event);
        }
    }

    /**
     * Dispatch the event if the boolean is true.
     *
     * @param  Closure $boolean
     * @param  mixed $event
     * @return void
     */
    public function dispatch_if(Closure $boolean, $event)
    {
        if ($boolean($event)) {
            $this->dispatch($event);
        }
    }

    /**
     * Dispatch the event unless the boolean is true.
     *
     * @param  Closure $boolean
     * @param  mixed $event
     * @return void
     */
    public function dispatch_unless(Closure $boolean, $event)
    {
        if (!$boolean($event)) {
            $this->dispatch($event);
        }
    }

    protected function resolve($listener, $event)
    {
        if (!is_subclass_of($listener, Listener::class)) {
            throw new InvalidArgumentException(sprintf(
                'The listener [%s] must be a subclass of [%s]',
                Listener::class
            ));
        }

        return (new $listener())->handle($event);
    }
}
