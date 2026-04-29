<?php

namespace Laragear\MultiAuth;

use Composer\Config;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Env;
use Laragear\MultiAuth\Support\Str;

class AuthManager
{
    /**
     * Supported authentication types by Composer, plus our custom types.
     */
    public const array AUTH_TYPES = [
        'http-basic',
        'github-oauth',
        'gitlab-oauth',
        'gitlab-token',
        'bearer',
        'license',
        'query',
    ];

    /**
     * Create a new Auth Manager instance.
     */
    public function __construct(
        protected Config $config,
        protected Arr $arr,
        protected Env $env,
        protected Str $str
    ) {
        //
    }

    /**
     * Get all package names that have custom authentication configured.
     *
     * @return string[]
     */
    public function getConfiguredPackages(): array
    {
        $packages = [];

        foreach (self::AUTH_TYPES as $type) {
            $configForType = $this->config->get($type);

            if ($this->arr->isArray($configForType)) {
                foreach ($this->arr->keys($configForType) as $key) {
                    // If it contains a slash, is 99% probable that is a package name.
                    if ($this->str->contains((string) $key, '/')) {
                        $packages[] = (string) $key;
                    }
                }
            }
        }

        return $this->arr->unique($packages);
    }

    /**
     * Resolve authentication details for a specific package.
     *
     * @param  string  $packageName
     * @return array<string, mixed>
     */
    public function resolveForPackage(string $packageName): array
    {
        $resolved = [];

        foreach (self::AUTH_TYPES as $type) {
            $configForType = $this->config->get($type);

            if ($this->arr->isArray($configForType)) {
                foreach ($configForType as $key => $credentials) {
                    // Use case-insensitive match just in case the cases do not match.
                    if ($this->str->caseCmp($key, $packageName) === 0) {
                        $resolved[$type] = $this->resolveEnvVariables($credentials);
                        break;
                    }
                }
            }
        }

        return $resolved;
    }

    /**
     * Recursively resolve environment variables by replacing `$VARIABLE` or `${VARIABLE}` with the env value.
     */
    protected function resolveEnvVariables(mixed $data): mixed
    {
        if ($this->arr->isArray($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->resolveEnvVariables($value);
            }

            return $data;
        }

        if ($this->str->isString($data)) {
            return $this->str->pregReplaceCallback(
                '/\$([A-Za-z0-9_]+)|\$\{([A-Za-z0-9_]+)}/', $this->resolveVariableMatch(...), $data
            );
        }

        return $data;
    }

    /**
     * Resolve a matched environment variable and return its value or the original match.
     *
     * @param  array<int, string>  $matches
     * @return string
     */
    protected function resolveVariableMatch(array $matches): string
    {
        $varName = $matches[1] ?: $matches[2];

        $value = $this->env->get($varName);

        return $value !== false ? $value : $matches[0];
    }
}
