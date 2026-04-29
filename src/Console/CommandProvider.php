<?php

namespace Laragear\MultiAuth\Console;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Laragear\MultiAuth\Factory;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Composer;
use Laragear\MultiAuth\Support\File;

/**
 * @codeCoverageIgnore
 */
class CommandProvider implements CommandProviderCapability
{
    /**
     * Create a new Command Provider instance.
     */
    public function __construct(
        protected Factory $factory,
        protected Arr $arr,
        protected Composer $composerHelper,
        protected File $file,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function getCommands(): array
    {
        return [
            $this->factory->makeConfigCommand($this->arr, $this->composerHelper, $this->file),
        ];
    }
}
