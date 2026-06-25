<?php

declare(strict_types=1);

namespace Symfony\Component\VarDumper;

/**
 * Minimal VarDumper stub for IDE static analysis.
 *
 * @see https://github.com/symfony/var-dumper
 */
class VarDumper
{
    /**
     * @param mixed $var
     *
     * @return mixed
     */
    public static function dump($var)
    {
        return null;
    }

    /**
     * @param callable|null $callable
     *
     * @return callable|null
     */
    public static function setHandler(?callable $callable = null)
    {
        return null;
    }
}
