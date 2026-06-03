<?php

declare(strict_types=1);

namespace Symfony\Component\Finder;

/**
 * Minimal Finder stub for php-scoper config static analysis.
 *
 * @see https://github.com/symfony/finder
 */
class Finder implements \IteratorAggregate, \Countable
{
    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return $this
     */
    public function files()
    {
        return $this;
    }

    /**
     * @param string|array $dirs
     *
     * @return $this
     */
    public function in($dirs)
    {
        return $this;
    }

    /**
     * @param string|array $patterns
     *
     * @return $this
     */
    public function exclude($patterns)
    {
        return $this;
    }

    /**
     * @param string|array $patterns
     *
     * @return $this
     */
    public function name($patterns)
    {
        return $this;
    }

    /**
     * @return \Traversable<int, SplFileInfo>
     */
    public function getIterator(): \Traversable
    {
        return new \EmptyIterator();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return 0;
    }
}
