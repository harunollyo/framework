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
     * @param array<ReflectionParameter> $parameters
     * @param array<mixed> $primitives
     *
     * @return array
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

    protected function resolve_primitive(ReflectionParameter $parameter, array $primitives = [])
    {
        $paramName = $parameter->getName();

        if (array_key_exists($paramName, $primitives)) {
            return $primitives[$paramName];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ReflectionException(sprintf(
            'Unable to resolve primitive parameter "%s" in class "%s".',
            $paramName,
            $parameter->getDeclaringClass()->getName()
        ));
    }

    /**
     * Resolve the dependencies for the given method.
     *
     * @param string|object $class
     * @param string $method
     * @return array<mixed>
     * @throws ReflectionException
     */
    protected function resolve_method_dependencies($class, string $method, array $primitives = [])
    {
        $class = is_object($class) ? get_class($class) : $class;

        $reflector = new ReflectionMethod($class, $method);
        $parameters = $reflector->getParameters();

        return $this->resolve_dependencies($parameters, $primitives);
    }
}