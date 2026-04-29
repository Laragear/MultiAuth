<?php

namespace Laragear\MultiAuth\Tests\Console;

use InvalidArgumentException;
use Laragear\MultiAuth\Console\ConfigCommand;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Composer;
use Laragear\MultiAuth\Support\File;
use Laragear\MultiAuth\Factory;
use Composer\Console\Application;
use Composer\Config as ComposerConfig;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;

class ConfigCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function getCommand(
        Arr $arr,
        Composer $composer,
        File $file,
        Factory $factory
    ): ConfigCommand {
        $command = new ConfigCommand($arr, $composer, $file, $factory);
        $command->setApplication(new Application());
        return $command;
    }

    public function test_is_registered_as_signature(): void
    {
        $arr = new Arr();
        $composer = m::mock(Composer::class);
        $fileProxy = m::mock(File::class);
        $factory = m::mock(Factory::class);

        $jsonFile = m::mock(JsonFile::class);
        $jsonFile->shouldReceive('exists')->andReturn(false);
        $jsonFile->shouldReceive('write')->with(['license' => ['laragear/pkg' => 'ABC-123']])->once();

        $factory->shouldReceive('makeJsonFile')->with('auth.json')->andReturn($jsonFile);

        $command = $this->getCommand($arr, $composer, $fileProxy, $factory);
        $tester = new CommandTester($command);

        $tester->execute([
            'package' => 'laragear/pkg',
            'type' => 'license',
            'credentials' => ['ABC-123']
        ]);

        static::assertEquals(0, $tester->getStatusCode());
    }

    public function test_adds_license_to_auth_json(): void
    {
        $arr = new Arr();
        $composer = m::mock(Composer::class);
        $fileProxy = m::mock(File::class);
        $factory = m::mock(Factory::class);

        $command = $this->getCommand($arr, $composer, $fileProxy, $factory);

        static::assertSame('multi-auth:config', $command->getName());
    }

    public function test_updates_existing_auth_json_globally(): void
    {
        $arr = new Arr();

        $mockConfig = m::mock(ComposerConfig::class);
        $mockConfig->shouldReceive('get')->with('home')->andReturn('/home/user');

        $composer = m::mock(Composer::class);
        $composer->shouldReceive('createConfig')->andReturn($mockConfig);

        $fileProxy = m::mock(File::class);
        $fileProxy->shouldReceive('getContents')->with('/home/user/auth.json')->andReturn('{"license": {}}');
        $fileProxy->shouldReceive('putContents')->with('/home/user/auth.json', 'new-content')->once();

        $jsonFile = m::mock(JsonFile::class);
        $jsonFile->shouldReceive('exists')->andReturn(true);
        $jsonFile->shouldReceive('read')->andReturn(['license' => []]);

        $manipulator = m::mock(JsonManipulator::class);
        $manipulator->shouldReceive('addMainKey')->with('license', ['laragear/pkg' => 'ABC-123'])->once();
        $manipulator->shouldReceive('getContents')->andReturn('new-content');

        $factory = m::mock(Factory::class);
        $factory->shouldReceive('makeJsonFile')->andReturn($jsonFile);
        $factory->shouldReceive('makeJsonManipulator')->andReturn($manipulator);

        $command = $this->getCommand($arr, $composer, $fileProxy, $factory);
        $tester = new CommandTester($command);

        $tester->execute([
            'package' => 'laragear/pkg',
            'type' => 'license',
            'credentials' => ['ABC-123'],
            '--global' => true
        ]);

        static::assertEquals(0, $tester->getStatusCode());
    }

    public function test_adds_query_to_auth_json(): void
    {
        $arr = new Arr();
        $composer = m::mock(Composer::class);
        $fileProxy = m::mock(File::class);
        $factory = m::mock(Factory::class);

        $jsonFile = m::mock(JsonFile::class);
        $jsonFile->shouldReceive('exists')->andReturn(false);
        $jsonFile->shouldReceive('write')->with(['query' => ['laragear/pkg' => ['auth' => ['license:$TOKEN']]]])->once();

        $factory->shouldReceive('makeJsonFile')->andReturn($jsonFile);

        $command = $this->getCommand($arr, $composer, $fileProxy, $factory);
        $tester = new CommandTester($command);

        $tester->execute([
            'package' => 'laragear/pkg',
            'type' => 'query',
            'credentials' => ['auth', 'license:$TOKEN']
        ]);

        static::assertEquals(0, $tester->getStatusCode());
    }

    public function test_adds_http_basic(): void
    {
        $this->expectNotToPerformAssertions();

        $arr = new Arr();
        $composer = m::mock(Composer::class);
        $fileProxy = m::mock(File::class);
        $factory = m::mock(Factory::class);

        $jsonFile = m::mock(JsonFile::class);
        $jsonFile->shouldReceive('exists')->andReturn(false);
        $jsonFile->shouldReceive('write')->with([
            'http-basic' => [
                'laragear/pkg' => [
                    'username' => 'foo',
                    'password' => 'bar',
                ]
            ]
        ]);
        $factory->shouldReceive('makeJsonFile')->andReturn($jsonFile);

        $command = $this->getCommand($arr, $composer, $fileProxy, $factory);
        $tester = new CommandTester($command);

        $tester->execute([
            'package' => 'laragear/pkg',
            'type' => 'http-basic',
            'credentials' => ['foo', 'bar']
        ]);
    }

    public function test_fails_http_basic_if_missing_credentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('http-basic requires username and password');

        $arr = new Arr();
        $composer = m::mock(Composer::class);
        $fileProxy = m::mock(File::class);
        $factory = m::mock(Factory::class);

        $jsonFile = m::mock(JsonFile::class);
        $jsonFile->shouldReceive('exists')->andReturn(false);
        $factory->shouldReceive('makeJsonFile')->andReturn($jsonFile);

        $command = $this->getCommand($arr, $composer, $fileProxy, $factory);
        $tester = new CommandTester($command);

        $tester->execute([
            'package' => 'laragear/pkg',
            'type' => 'http-basic',
            'credentials' => ['user123']
        ]);
    }
}
