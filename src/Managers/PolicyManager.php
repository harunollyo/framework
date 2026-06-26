<?php
/**
 * Registers and resolves authorization policies for framework models.
 * Maps model classes to policy handlers and evaluates user abilities through the Guard facade.
 * Centralizes access control decisions for controllers and domain services.
 *
 * @package    Framework
 * @subpackage Managers
 * @since      1.0.0
 */
namespace Framework\Managers;

defined('ABSPATH') || exit;

use Exception;
use Framework\Concerns\DependencyResolvable;
use Framework\Exceptions\AuthorizationException;
use Framework\Supports\Arr;
use InvalidArgumentException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function Framework\app;
use function Framework\config_path;
use function Framework\message;
use function Framework\user;

class PolicyManager
{
    use DependencyResolvable;

    /**
     * The array of registered policies or the policies data from cache.
     *
     * @var array
     *
     * @since 1.0.0
     */
    protected $policies = [];

    /**
     * The currently resolved user.
     *
     * @var mixed
     *
     * @since 1.0.0
     */
    protected $user;

    /**
     * PolicyManager constructor.
     *
     * Loads and registers policies from the cache file.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->load_policies();
        $this->register_policies();
    }

    /**
     * Load the policies from the cached policies file.
     *
     * @return $this|null
     *
     * @since 1.0.0
     */
    protected function load_policies()
    {
        $policies_cache_path = config_path('policies.cache.php');

        if (!file_exists($policies_cache_path)) {
            return;
        }

        $this->policies = require $policies_cache_path;

        return $this;
    }

    /**
     * Register the loaded policies for each model.
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function register_policies()
    {
        foreach ($this->policies as $policy) {
            $this->register_policy($policy['model'], $policy['policy']);
        }

        return $this;
    }

    /**
     * Register an individual model-policy mapping.
     *
     * @param string $model The model instance.
     * @param string $policy The policy.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register_policy(string $model, string $policy)
    {
        $this->policies[$model] = $policy;
    }

    /**
     * Resolve the policy object for a given model.
     *
     * @param mixed $model The model instance.
     *
     * @return mixed|null
     *
     * @since 1.0.0
     */
    protected function resolve_policy($model)
    {
        $model = is_object($model) ? get_class($model) : $model;

        if (!$this->has_policy($model)) {
            return null;
        }

        return app()->make($this->policy($model));
    }

    /**
     * Determine if a policy exists for the given model.
     *
     * @param mixed $model The model instance.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function has_policy($model)
    {
        return isset($this->policies[$model]);
    }

    /**
     * Get the class name of the policy for the given model.
     *
     * @param mixed $model The model instance.
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function policy($model)
    {
        return $this->policies[$model];
    }

    /**
     * Authorize an ability against a model instance or class.
     *
     * @param string $ability The ability.
     * @param mixed $model The model instance.
     * @param array $arguments The method arguments.
     *
     * @return bool|mixed
     *
     * @throws \AuthorizationException
     * @throws \InvalidArgumentException
     *
     * @since 1.0.0
     */
    public function authorize(string $ability, $model = null, array $arguments = [])
    {
        $user = $this->get_current_user();

        if (!$user->is_logged_in()) {
            throw new AuthorizationException(message('auth.logged_in_required'));
        }

        $policy = $this->resolve_policy($model);

        if (!$policy) {
            throw new AuthorizationException(message('auth.no_policy'));
        }

        if (method_exists($policy, 'before')) {
            $before_result = $policy->before($user, $ability);

            if ($before_result === true) {
                return true;
            }

            if ($before_result === false) {
                throw new AuthorizationException(
                    message('auth.unauthorized_action', $ability)
                );
            }
        }

        if (!method_exists($policy, $ability)) {
            throw new AuthorizationException(
                message('auth.ability_not_defined', $ability)
            );
        }

        if (!empty($arguments) && !Arr::is_associative($arguments)) {
            throw new InvalidArgumentException('The 3rd parameter must be an associative array.');
        }

        $dependencies = $this->resolve_method_dependencies($policy, $ability, $arguments);

        $can_perform = $policy->$ability(...$dependencies);

        if (!$can_perform) {
            throw new AuthorizationException(
                message('auth.unauthorized_action', $ability)
            );
        }

        return true;
    }

    /**
     * Determine if the current user is allowed to perform the given ability.
     *
     * @param string $ability The ability.
     * @param mixed $model The model instance.
     * @param array $arguments The method arguments.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function allows(string $ability, $model = null, array $arguments = [])
    {
        try {
            return $this->authorize($ability, $model, $arguments);
        } catch (AuthorizationException $exception) {
            return false;
        }
    }

    /**
     * Determine if the current user is denied from performing the given ability.
     *
     * @param string $ability The ability.
     * @param mixed $model The model instance.
     * @param array $arguments The method arguments.
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function denies(string $ability, $model = null, array $arguments = [])
    {
        return !$this->allows($ability, $model, $arguments);
    }

    /**
     * Get the current user object.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    protected function get_current_user()
    {
        return user();
    }
}
