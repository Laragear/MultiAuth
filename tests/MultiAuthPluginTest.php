<?php

namespace Laragear\MultiAuth\Tests;

use Composer\Plugin\Capability\CommandProvider;
use Laragear\MultiAuth\MultiAuthPlugin;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Str;
use Laragear\MultiAuth\Factory;
use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Package\PackageInterface;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;

class MultiAuthPluginTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function getPlugin(): MultiAuthPlugin
    {
        return new MultiAuthPlugin();
    }

    public function test_plugin_registers_events_and_commands(): void
    {
        static::assertArrayHasKey('pre-file-download', MultiAuthPlugin::getSubscribedEvents());

        $plugin = $this->getPlugin();
        $capabilities = $plugin->getCapabilities();
        static::assertArrayHasKey(CommandProvider::class, $capabilities);
    }

    public function test_activate_deactivate_uninstall(): void
    {
        $mock = m::mock(Composer::class);
        $mock->shouldReceive('getConfig')->andReturn(m::mock(Config::class));
        $io = m::mock(IOInterface::class);

        $plugin = $this->getPlugin();
        $plugin->activate($mock, $io);
        $plugin->deactivate($mock, $io);
        $plugin->uninstall($mock, $io);

        static::assertTrue(true);
    }

    public function test_plugin_modifies_request_if_package_matches_context(): void
    {
        $mock = m::mock(Config::class);
        $mock->shouldReceive('get')->with('license')->andReturn(['laragear/pkg' => 'ABC-123']);
        $mock->shouldReceive('get')->andReturn(null);

        $composer = m::mock(Composer::class);
        $composer->shouldReceive('getConfig')->andReturn($mock);

        $io = m::mock(IOInterface::class);
        $io->shouldReceive('isDebug')->andReturn(true);
        $io->shouldReceive('write')->with("  - <info>Applying multi-auth headers for package laragear/pkg</info>");

        $plugin = $this->getPlugin();
        $plugin->activate($composer, $io);

        $package = m::mock(PackageInterface::class);
        $package->shouldReceive('getName')->andReturn('laragear/pkg');

        $event = m::mock(PreFileDownloadEvent::class);
        $event->shouldReceive('getProcessedUrl')->andReturn('https://example.com/file.zip');
        $event->shouldReceive('getContext')->andReturn($package);

        $event->shouldReceive('getTransportOptions')->andReturn(['http' => ['header' => ['Foo: Bar']]]);

        $event->shouldReceive('setTransportOptions')->with([
            'http' => [
                'header' => [
                    'Foo: Bar',
                    'Authentication: License ABC-123'
                ]
            ]
        ])->once();

        $plugin->onPreFileDownload($event);
    }

    public function test_plugin_modifies_request_if_package_matches_url(): void
    {
        $mock = m::mock(Config::class);
        $mock->shouldReceive('get')->with('license')->andReturn(['laragear/pkg' => 'ABC-123']);
        $mock->shouldReceive('get')->andReturn(null);

        $composer = m::mock(Composer::class);
        $composer->shouldReceive('getConfig')->andReturn($mock);

        $io = m::mock(IOInterface::class);
        $io->shouldReceive('isDebug')->andReturn(false);

        $plugin = $this->getPlugin();
        $plugin->activate($composer, $io);

        $event = m::mock(PreFileDownloadEvent::class);
        // URL contains /laragear/pkg
        $event->shouldReceive('getProcessedUrl')->andReturn('https://example.com/p2/laragear/pkg.json');
        $event->shouldReceive('getContext')->andReturn('something_else');

        $event->shouldReceive('getTransportOptions')->andReturn([]);

        $event->shouldReceive('setTransportOptions')->with([
            'http' => [
                'header' => [
                    'Authentication: License ABC-123'
                ]
            ]
        ])->once();

        $plugin->onPreFileDownload($event);
    }

    public function test_plugin_modifies_url_if_query_credentials_exist(): void
    {
        $mock = m::mock(Config::class);
        $mock->shouldReceive('get')->with('query')->andReturn(['laragear/pkg' => ['token' => 'abc']]);
        $mock->shouldReceive('get')->andReturn(null);

        $composer = m::mock(Composer::class);
        $composer->shouldReceive('getConfig')->andReturn($mock);

        $io = m::mock(IOInterface::class);
        $io->shouldReceive('isDebug')->andReturn(true);
        $io->shouldReceive('write')->with("  - <info>Applying multi-auth query parameters for package laragear/pkg</info>");

        $plugin = $this->getPlugin();
        $plugin->activate($composer, $io);

        $package = m::mock(PackageInterface::class);
        $package->shouldReceive('getName')->andReturn('laragear/pkg');

        $event = m::mock(PreFileDownloadEvent::class);
        $event->shouldReceive('getProcessedUrl')->andReturn('https://example.com/file.zip');
        $event->shouldReceive('getContext')->andReturn($package);

        $event->shouldReceive('setProcessedUrl')->with('https://example.com/file.zip?token=abc')->once();

        $plugin->onPreFileDownload($event);
    }

    public function test_on_pre_file_download_does_nothing_if_no_package_match(): void
    {
        $mock = m::mock(Config::class);
        $mock->shouldReceive('get')->andReturn(null);

        $composer = m::mock(Composer::class);
        $composer->shouldReceive('getConfig')->andReturn($mock);

        $plugin = $this->getPlugin();
        $plugin->activate($composer, m::mock(IOInterface::class));

        $event = m::mock(PreFileDownloadEvent::class);
        $event->shouldReceive('getProcessedUrl')->andReturn('https://example.com/unknown.zip');
        $event->shouldReceive('getContext')->andReturn(null);

        $event->shouldNotReceive('setTransportOptions');
        $event->shouldNotReceive('setProcessedUrl');

        $plugin->onPreFileDownload($event);
    }
}
