<?php

namespace Laragear\MultiAuth;

use Composer\Config;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Env;
use Laragear\MultiAuth\Support\Str;

/**
 * @codeCoverageIgnore
 */
class Factory
{
    /**
     * Create a new AuthManager instance.
     */
    public function makeAuthManager(Config $config): AuthManager
    {
        return new AuthManager($config, $this->makeArr(), $this->makeEnv(), $this->makeStr());
    }

    /**
     * Create a new ConfigCommand instance.
     */
    public function makeConfigCommand(Arr $arr, Support\Composer $composer, Support\File $file): Console\ConfigCommand
    {
        return new Console\ConfigCommand($arr, $composer, $file, $this);
    }

    /**
     * Create a new RequestModifier instance.
     */
    public function makeRequestModifier(): RequestModifier
    {
        return new RequestModifier($this->makeStr());
    }

    /**
     * Create a new JsonFile instance.
     */
    public function makeJsonFile(string $path): JsonFile
    {
        return new JsonFile($path);
    }

    /**
     * Create a new JsonManipulator instance.
     */
    public function makeJsonManipulator(string $contents): JsonManipulator
    {
        return new JsonManipulator($contents);
    }

    /**
     * Create a new Arr helper instance.
     */
    public function makeArr(): Arr
    {
        return new Arr();
    }

    /**
     * Create a new Env helper instance.
     */
    public function makeEnv(): Env
    {
        return new Env();
    }

    /**
     * Create a new Str helper instance.
     */
    public function makeStr(): Str
    {
        return new Str();
    }
}
