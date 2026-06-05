<?php

namespace Framework\Filesystem;

class Path
{
    /**
     * Join path segments onto a base path.
     *
     * @param string $base
     * @param string ...$paths
     *
     * @return string
     * @since 1.0.0
     */
    public static function join($base, ...$paths)
    {
        foreach ($paths as $index => $path) {
            if (empty($path) && $path !== '0') {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return $base . implode('', $paths);
    }

    /**
     * Normalize a filesystem path without requiring it to exist.
     *
     * @param string $path
     *
     * @return string
     * @since 1.0.0
     */
    public static function normalize($path)
    {
        $path = str_replace('\\', '/', $path);
        $is_absolute = $path !== '' && $path[0] === '/';
        $parts = [];

        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                if (!empty($parts)) {
                    array_pop($parts);
                }

                continue;
            }

            $parts[] = $part;
        }

        $normalized = implode('/', $parts);

        if ($is_absolute) {
            return '/' . $normalized;
        }

        return $normalized;
    }
}
