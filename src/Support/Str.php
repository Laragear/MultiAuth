<?php

namespace Laragear\MultiAuth\Support;

/**
 * Helper class to handle native string functions.
 *
 * @codeCoverageIgnore
 */
class Str
{
    /**
     * Determine if a given string contains a given substring.
     */
    public function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Binary safe case-insensitive string comparison.
     */
    public function caseCmp(string $string1, string $string2): int
    {
        return strcasecmp($string1, $string2);
    }

    /**
     * Find whether the type of a variable is string.
     */
    public function isString(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * Perform a regular expression search and replace using a callback.
     */
    public function pregReplaceCallback(string|array $pattern, callable $callback, string|array $subject, int $limit = -1, ?int &$count = null, int $flags = 0): string|array|null
    {
        return preg_replace_callback($pattern, $callback, $subject, $limit, $count, $flags);
    }

    /**
     * Perform a regular expression search and replace.
     */
    public function pregReplace(string|array $pattern, string|array $replacement, string|array $subject, int $limit = -1, ?int &$count = null): string|array|null
    {
        return preg_replace($pattern, $replacement, $subject, $limit, $count);
    }

    /**
     * Encodes data with MIME base64.
     */
    public function base64Encode(string $string): string
    {
        return base64_encode($string);
    }

    /**
     * Generate URL-encoded query string.
     */
    public function httpBuildQuery(mixed $data, string $numeric_prefix = "", string|null $arg_separator = null, int $encoding_type = PHP_QUERY_RFC1738): string
    {
        return http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type);
    }
}
