<?php

namespace Laragear\MultiAuth\Support;

/**
 * Helper class to handle native environment functions.
 *
 * @codeCoverageIgnore
 */
class Env
{
    /**
     * Get an environment variable.
     */
    public function get(string $name, bool $local_only = false): string|array|false
    {
        return getenv($name, $local_only);
    }
}
