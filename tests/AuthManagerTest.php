<?php

namespace Laragear\MultiAuth\Tests;

use Laragear\MultiAuth\AuthManager;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Env;
use Laragear\MultiAuth\Support\Str;
use Composer\Config;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;

class AuthManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        putenv('MY_TEST_TOKEN=secret123');
    }

    protected function tearDown(): void
    {
        putenv('MY_TEST_TOKEN');
    }

    protected function getAuthManager(Config $mock): AuthManager
    {
        return new AuthManager($mock, new Arr(), new Env(), new Str());
    }

    public static function authConfigProvider(): array
    {
        return [
            'http_basic_match' => [
                'laragear/pkg',
                [
                    'http-basic' => [
                        'laragear/pkg' => ['username' => 'foo', 'password' => 'bar'],
                        'other/pkg' => ['username' => 'baz', 'password' => 'qux'],
                    ]
                ],
                [
                    'http-basic' => ['username' => 'foo', 'password' => 'bar']
                ]
            ],
            'license_match' => [
                'laragear/pkg',
                [
                    'license' => [
                        'laragear/pkg' => 'ABC-123',
                    ]
                ],
                [
                    'license' => 'ABC-123'
                ]
            ],
            'env_variable_resolution_string' => [
                'laragear/pkg',
                [
                    'bearer' => [
                        'laragear/pkg' => '$MY_TEST_TOKEN',
                    ]
                ],
                [
                    'bearer' => 'secret123'
                ]
            ],
            'env_variable_resolution_array' => [
                'laragear/pkg',
                [
                    'query' => [
                        'laragear/pkg' => [
                            'auth' => ['license:${MY_TEST_TOKEN}']
                        ],
                    ]
                ],
                [
                    'query' => [
                        'auth' => ['license:secret123']
                    ]
                ]
            ],
            'env_variable_not_found' => [
                'laragear/pkg',
                [
                    'bearer' => [
                        'laragear/pkg' => '$NON_EXISTENT',
                    ]
                ],
                [
                    'bearer' => '$NON_EXISTENT'
                ]
            ],
            'case_insensitive_match' => [
                'LaraGear/Pkg',
                [
                    'http-basic' => [
                        'laragear/pkg' => ['username' => 'foo', 'password' => 'bar'],
                    ]
                ],
                [
                    'http-basic' => ['username' => 'foo', 'password' => 'bar']
                ]
            ],
            'no_match' => [
                'laragear/pkg',
                [
                    'http-basic' => [
                        'other/pkg' => ['username' => 'baz', 'password' => 'qux'],
                    ]
                ],
                []
            ],
            'non_array_config' => [
                'laragear/pkg',
                [
                    'http-basic' => 'not-an-array',
                ],
                []
            ],
            'non_string_data' => [
                'laragear/pkg',
                [
                    'license' => [
                        'laragear/pkg' => 123,
                    ]
                ],
                [
                    'license' => 123
                ]
            ]
        ];
    }

    #[DataProvider('authConfigProvider')]
    public function test_resolves_credentials_for_package(string $package, array $configSetup, array $expected): void
    {
        $mock = m::mock(Config::class);

        foreach (AuthManager::AUTH_TYPES as $type) {
            $mock->shouldReceive('get')->with($type)->andReturn($configSetup[$type] ?? null);
        }

        $manager = $this->getAuthManager($mock);

        static::assertEquals($expected, $manager->resolveForPackage($package));
    }

    public function test_get_configured_packages(): void
    {
        $mock = m::mock(Config::class);
        $mock->shouldReceive('get')->with('http-basic')->andReturn([
            'laragear/pkg1' => [],
            'laragear/pkg2' => [],
            'not-a-package' => [], // Should be filtered if no slash
        ]);
        $mock->shouldReceive('get')->with('license')->andReturn([
            'laragear/pkg1' => 'ABC',
            'laragear/pkg3' => 'XYZ',
        ]);
        $mock->shouldReceive('get')->andReturn(null);

        $manager = $this->getAuthManager($mock);

        $packages = $manager->getConfiguredPackages();
        sort($packages);

        static::assertEquals(['laragear/pkg1', 'laragear/pkg2', 'laragear/pkg3'], $packages);
    }

    public function test_get_configured_packages_handles_non_array(): void
    {
        $mock = m::mock(Config::class);
        $mock->shouldReceive('get')->andReturn('string');

        $manager = $this->getAuthManager($mock);

        static::assertEmpty($manager->getConfiguredPackages());
    }
}
