<?php

namespace Laragear\MultiAuth\Support;

use Composer\Config;
use Composer\Factory;

/**
 * Helper class to handle Composer static methods.
 *
 * @codeCoverageIgnore
 */
class Composer
{
    /**
     * Create a new Composer Config instance.
     */
    public function createConfig(): Config
    {
        return Factory::createConfig();
    }
}
