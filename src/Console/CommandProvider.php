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
    protected Factory $factory;
    protected Arr $arr;
    protected Composer $composerHelper;
    protected File $file;

    /**
     * @inheritDoc
     */
    public function getCommands(): array
    {
        return [
            $this->getFactory()->makeConfigCommand($this->getArr(), $this->getComposerHelper(), $this->getFile()),
        ];
    }

    /**
     * @return \Laragear\MultiAuth\Factory
     */
    public function getFactory(): Factory
    {
        return $this->factory ??= new Factory();
    }

    /**
     * @param  \Laragear\MultiAuth\Factory  $factory
     */
    public function setFactory(Factory $factory): void
    {
        $this->factory = $factory;
    }

    /**
     * @return \Laragear\MultiAuth\Support\Arr
     */
    public function getArr(): Arr
    {
        return $this->arr ??= new Arr();
    }

    /**
     * @param  \Laragear\MultiAuth\Support\Arr  $arr
     */
    public function setArr(Arr $arr): void
    {
        $this->arr = $arr;
    }

    /**
     * @return \Laragear\MultiAuth\Support\Composer
     */
    public function getComposerHelper(): Composer
    {
        return $this->composerHelper ??= new Composer();
    }

    /**
     * @param  \Laragear\MultiAuth\Support\Composer  $composerHelper
     */
    public function setComposerHelper(Composer $composerHelper): void
    {
        $this->composerHelper = $composerHelper;
    }

    /**
     * @return \Laragear\MultiAuth\Support\File
     */
    public function getFile(): File
    {
        return $this->file ??= new File();
    }

    /**
     * @param  \Laragear\MultiAuth\Support\File  $file
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }
}
