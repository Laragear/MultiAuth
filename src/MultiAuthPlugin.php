<?php

namespace Laragear\MultiAuth;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Laragear\MultiAuth\Console\CommandProvider;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Str;
use Laragear\MultiAuth\Factory;

class MultiAuthPlugin implements PluginInterface, EventSubscriberInterface, Capable
{
    protected Composer $composer;
    protected IOInterface $io;
    protected AuthManager $authManager;
    protected RequestModifier $requestModifier;
    protected Factory $factory;
    protected Arr $arr;
    protected Str $str;

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->factory = new Factory();

        $this->authManager = $this->factory->makeAuthManager($composer->getConfig());
        $this->requestModifier = $this->factory->makeRequestModifier();
        $this->arr = $this->factory->makeArr();
        $this->str = $this->factory->makeStr();
    }

    /**
     * @inheritDoc
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities(): array
    {
        return [
            \Composer\Plugin\Capability\CommandProvider::class => CommandProvider::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD => 'onPreFileDownload',
        ];
    }

    /**
     * Add a listener before downloading the artifact.
     */
    public function onPreFileDownload(PreFileDownloadEvent $event): void
    {
        $configuredPackages = $this->authManager->getConfiguredPackages();

        $packageName = $this->retrievePackageName($event->getContext(), $event->getProcessedUrl(), $configuredPackages);

        // Apply if package name is found and it has configuration
        if ($packageName !== null && $this->arr->in($packageName, $configuredPackages, true)) {
            $credentials = $this->authManager->resolveForPackage($packageName);

            if (!empty($credentials)) {
                $this->applyCredentials($event, $credentials, $packageName);
            }
        }
    }

    /**
     * Retrieve the package name or infer it from the download URL.
     *
     * @param  string[]  $configuredPackages
     */
    protected function retrievePackageName(mixed $context, string $url, array $configuredPackages): ?string
    {
        if ($context instanceof PackageInterface) {
            return $context->getName();
        }

        return $this->arr->find($configuredPackages, function ($pkg) use ($url): bool {
            // Add slashes to ensure it's a path segment matching the package, minimizing false positives.
            return $this->str->contains($url, '/'.$pkg);
        });
    }

    /**
     * Apply the found credentials to the Request to download the package.
     *
     * @param  array<string, string[]>  $credentials
     */
    protected function applyCredentials(PreFileDownloadEvent $event, array $credentials, string $packageName): void
    {
        $headers = $this->requestModifier->buildHeaders($credentials);

        if (!empty($headers)) {
            $options = $event->getTransportOptions();

            if (!isset($options['http']['header'])) {
                $options['http']['header'] = [];
            }

            // Merge headers, keeping existing ones
            $existingHeaders = (array) $options['http']['header'];
            $options['http']['header'] = $this->arr->merge($existingHeaders, $headers);

            $event->setTransportOptions($options);

            if ($this->io->isDebug()) {
                $this->io->write("  - <info>Applying multi-auth headers for package $packageName</info>");
            }
        }

        if (!empty($credentials['query'])) {
            $newUrl = $this->requestModifier->buildUrl($event->getProcessedUrl(), $credentials);
            $event->setProcessedUrl($newUrl);

            if ($this->io->isDebug()) {
                $this->io->write("  - <info>Applying multi-auth query parameters for package $packageName</info>");
            }
        }
    }
}
