<?php

namespace Framework\Managers;

use Framework\Exceptions\AuthorizationException;

use function Framework\app;
use function Framework\config_path;
use function Framework\user;

/**
 * Class PolicyManager
 *
 * Handles registration and authorization of model policies.
 */
class PolicyManager
{
    /**
     * The array of registered policies or the policies data from cache.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * The currently resolved user.
     *
     * @var mixed
     */
    protected $user;

    /**
     * PolicyManager constructor.
     *
     * Loads and registers policies from the cache file.
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
     * @param string $model
     * @param string $policy
     * @return void
     */
    public function register_policy(string $model, string $policy)
    {
        $this->policies[$model] = $policy;
    }

    /**
     * Resolve the policy object for a given model.
     *
     * @param mixed $model
     * @return mixed|null
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
     * @param string $model
     * @return bool
     */
    protected function has_policy($model)
    {
        return isset($this->policies[$model]);
    }

    /**
     * Get the class name of the policy for the given model.
     *
     * @param string $model
     * @return string
     */
    protected function policy($model)
    {
        return $this->policies[$model];
    }

    /**
     * Authorize an ability against a model instance or class.
     *
     * @param string $ability
     * @param mixed $model
     * @return bool|mixed
     * @throws AuthorizationException
     */
    public function authorize(string $ability, $model = null)
    {
        $user = $this->get_current_user();

        if (!$user->is_logged_in()) {
            throw new AuthorizationException('You have to be logged in');
        }

        $policy = $this->resolve_policy($model);

        if (!$policy) {
            throw new AuthorizationException('No policy found for this resource.');
        }

        if (method_exists($policy, 'before')) {
            if (($result = $policy->before($user, $ability)) !== null) {
                return $result;
            }
        }

        if (!method_exists($policy, $ability)) {
            throw new AuthorizationException(
                sprintf(
                    'The ability %s is not defined in the policy for this resource.',
                    $ability
                )
            );
        }

        $can_perform = $model
            ? $policy->$ability($user, $model)
            : $policy->$ability($user);

        if (!$can_perform) {
            throw new AuthorizationException(
                sprintf('You are not authorized to %s this resource.', $ability)
            );
        }

        return true;
    }

    /**
     * Determine if the current user is allowed to perform the given ability.
     *
     * @param string $ability
     * @param mixed $model
     * @return bool
     */
    public function allows(string $ability, $model = null)
    {
        try {
            return $this->authorize($ability, $model);
        } catch (AuthorizationException $exception) {
            return false;
        }
    }

    /**
     * Determine if the current user is denied from performing the given ability.
     *
     * @param string $ability
     * @param mixed $model
     * @return bool
     */
    public function denies(string $ability, $model = null)
    {
        return !$this->allows($ability, $model);
    }

    /**
     * Get the current user object.
     *
     * @return mixed
     */
    protected function get_current_user()
    {
        return user();
    }
}
