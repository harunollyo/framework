<?php
/**
 * Abstract base for application service providers with register and boot lifecycle hooks.
 * Receives the Application instance and binds services in register while wiring hooks in boot.
 * Pattern for modular plugin feature registration.
 *
 * @package Framework
 * @since   1.0.0
 */
namespace Framework;

defined('ABSPATH') || exit;

abstract class ServiceProvider
{
    /**
 * @var Application 
*/
    protected $app;

    /**
     * Create a new service provider constructor
     *
     * @param  Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    abstract public function register();

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
