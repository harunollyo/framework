<?php

namespace Framework\Database\Concerns;

use InvalidArgumentException;

trait HasDictionary
{
    /**
     * Get the dictionary key for the attribute.
     *
     * @param mixed $attribute The attribute to get the key for
     * @return string The dictionary key
     * @since 1.0.0
     */
    protected function get_dictionary_key($attribute)
    {
        if (is_null($attribute) || is_string($attribute) || is_int($attribute)) {
            return $attribute;
        }

        if (is_object($attribute)) {
            if (method_exists($attribute, '__toString')) {
                return $attribute->__toString();
            }

            throw new InvalidArgumentException('Attribute must be a string, integer, or object with a __toString method.');
        }

        return (string) $attribute;
    }
}