<?php

namespace Laragear\MultiAuth\Support;

/**
 * Helper class to handle native file functions.
 *
 * @codeCoverageIgnore
 */
class File
{
    /**
     * Reads entire file into a string.
     */
    public function getContents(string $filename, bool $use_include_path = false, mixed $context = null, int $offset = 0, ?int $length = null): string|false
    {
        return file_get_contents($filename, $use_include_path, $context, $offset, $length);
    }

    /**
     * Write a string to a file.
     */
    public function putContents(string $filename, mixed $data, int $flags = 0, mixed $context = null): int|false
    {
        return file_put_contents($filename, $data, $flags, $context);
    }
}
