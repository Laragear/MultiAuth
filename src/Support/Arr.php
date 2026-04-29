<?php

namespace Laragear\MultiAuth\Support;

/**
 * Helper class to handle native array functions.
 *
 * @codeCoverageIgnore
 */
class Arr
{
    /**
     * Determine if a given value is an array.
     */
    public function isArray(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * Return all the keys or a subset of the keys of an array.
     */
    public function keys(array $array): array
    {
        return array_keys($array);
    }

    /**
     * Removes duplicate values from an array.
     */
    public function unique(array $array): array
    {
        return array_unique($array);
    }

    /**
     * Checks if a value exists in an array.
     */
    public function in(mixed $needle, array $haystack, bool $strict = false): bool
    {
        return in_array($needle, $haystack, $strict);
    }

    /**
     * Returns the first element of an array that satisfies the callback.
     */
    public function find(array $array, callable $callback): ?string
    {
        foreach ($array as $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Merge one or more arrays.
     */
    public function merge(array ...$arrays): array
    {
        return array_merge(...$arrays);
    }

    /**
     * Count all elements in an array, or something in an object.
     */
    public function count(array $array): int
    {
        return count($array);
    }

    /**
     * Split an array into chunks.
     */
    public function chunk(array $array, int $length, bool $preserve_keys = false): array
    {
        return array_chunk($array, $length, $preserve_keys);
    }
}
