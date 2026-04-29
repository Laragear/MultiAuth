<?php

namespace Laragear\MultiAuth\Tests;

use Laragear\MultiAuth\RequestModifier;
use Laragear\MultiAuth\Support\Str;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RequestModifierTest extends TestCase
{
    protected function getRequestModifier(): RequestModifier
    {
        return new RequestModifier(new Str());
    }

    public static function providesHeaders(): array
    {
        return [
            'http_basic' => [
                ['http-basic' => ['username' => 'foo', 'password' => 'bar']],
                ['Authorization: Basic Zm9vOmJhcg==']
            ],
            'http_basic_partial' => [
                ['http-basic' => ['username' => 'foo']],
                []
            ],
            'bearer' => [
                ['bearer' => 'secret-token'],
                ['Authorization: Bearer secret-token']
            ],
            'github_oauth' => [
                ['github-oauth' => 'gh-token'],
                ['Authorization: token gh-token']
            ],
            'gitlab_oauth' => [
                ['gitlab-oauth' => 'gl-token'],
                ['Authorization: Bearer gl-token']
            ],
            'gitlab_token' => [
                ['gitlab-token' => 'gl-private'],
                ['Private-Token: gl-private']
            ],
            'license' => [
                ['license' => 'ABC-123'],
                ['Authentication: License ABC-123']
            ],
            'multiple' => [
                [
                    'http-basic' => ['username' => 'foo', 'password' => 'bar'],
                    'license' => 'ABC-123'
                ],
                [
                    'Authorization: Basic Zm9vOmJhcg==',
                    'Authentication: License ABC-123'
                ]
            ],
            'empty' => [
                [],
                []
            ]
        ];
    }

    #[DataProvider('providesHeaders')]
    public function test_builds_headers(array $credentials, array $expectedHeaders): void
    {
        $modifier = $this->getRequestModifier();
        static::assertEquals($expectedHeaders, $modifier->buildHeaders($credentials));
    }

    public static function providesUrl(): array
    {
        return [
            'no_query' => [
                'https://example.com/p2/pkg.json',
                [],
                'https://example.com/p2/pkg.json'
            ],
            'simple_query' => [
                'https://example.com/p2/pkg.json',
                ['query' => ['token' => 'abc']],
                'https://example.com/p2/pkg.json?token=abc'
            ],
            'existing_query' => [
                'https://example.com/p2/pkg.json?foo=bar',
                ['query' => ['token' => 'abc']],
                'https://example.com/p2/pkg.json?foo=bar&token=abc'
            ],
            'array_query' => [
                'https://example.com/p2/pkg.json',
                ['query' => ['auth' => ['license:123']]],
                'https://example.com/p2/pkg.json?auth=license%3A123'
            ],
            'multiple_array_query' => [
                'https://example.com/p2/pkg.json',
                ['query' => ['auth' => ['license:123', 'other:456']]],
                'https://example.com/p2/pkg.json?auth=license%3A123&auth=other%3A456'
            ]
        ];
    }

    #[DataProvider('providesUrl')]
    public function test_builds_url(string $url, array $credentials, string $expectedUrl): void
    {
        $modifier = $this->getRequestModifier();
        static::assertEquals($expectedUrl, $modifier->buildUrl($url, $credentials));
    }
}
