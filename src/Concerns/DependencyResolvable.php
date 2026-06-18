<?php
/**
 * Trait that resolves method parameters via reflection and the application container.
 * Injects typed class dependencies automatically and fills primitive arguments from a provided map or defaults.
 * Powers controller and route action resolution without manual wiring.
 *
 * @package    Framework
 * @subpackage Concerns
 * @since      1.0.0
 */
namespace Framework\Concerns;

defined('ABSPATH') || exit;

use Exception;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function Framework\app;

trait DependencyResolvable
{
    /**
     * Resolve the dependencies for the given parameters.
     *
     * @param array $parameters The parameters array.
     * @param array $primitives The primitives.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function resolve_dependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();

            if ($dependency === null || $dependency->isBuiltin()) {
                // Handle primitive parameters
                $dependencies[] = $this->resolve_primitive($parameter, $primitives);
            } else {
                // Handle class parameters
                $dependency_name = $dependency instanceof ReflectionNamedType ? $dependency->getName() : (string) $dependency;
                $dependencies[] = app()->make($dependency_name);
            }
        }

        return $dependencies;
    }

    /**
     * Resolve primitive.
     *
     * @param ReflectionParameter $parameter The parameter.
     * @param array $primitives The primitives.
     *
     * @return void
     *
     * @throws \ReflectionException
     *
     * @since 1.0.0
     */
    protected function resolve_primitive(ReflectionParameter $parameter, array $primitives = [])
    {
        $param_name = $parameter->getName();

        if (array_key_exists($param_name, $primitives)) {
            return $primitives[$param_name];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ReflectionException(sprintf(
            'Unable to resolve primitive parameter "%s" in class "%s".',
            $param_name,
            $parameter->getDeclaringClass()->getName()
        ));
    }

    /**
     * Resolve the dependencies for the given method.
     *
     * @param mixed $class The class.
     * @param string $method The method name.
     * @param array $primitives The primitives.
     *
     * @return array<mixed>
     *
     * @since 1.0.0
     */
    protected function resolve_method_dependencies($class, string $method, array $primitives = [])
    {
        $class = is_object($class) ? get_class($class) : $class;

        $reflector = new ReflectionMethod($class, $method);
        $parameters = $reflector->getParameters();

        return $this->resolve_dependencies($parameters, $primitives);
    }
}
