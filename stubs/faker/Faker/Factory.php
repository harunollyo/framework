<?php

declare(strict_types=1);

namespace Faker;

/**
 * Minimal Factory stub for IDE static analysis.
 *
 * @see https://github.com/FakerPHP/Faker
 */
class Factory
{
    /**
     * @param string $locale
     *
     * @return Generator
     */
    public static function create($locale = 'en_US')
    {
        return new Generator();
    }
}
