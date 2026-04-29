<?php

namespace Laragear\MultiAuth;

use Laragear\MultiAuth\Support\Str;

class RequestModifier
{
    public function __construct(
        protected Str $str
    ) {
    }
    /**
     * Build HTTP headers from resolved credentials.
     *
     * @param  array<string, mixed>  $credentials
     * @return array<string>
     */
    public function buildHeaders(array $credentials): array
    {
        $headers = [];

        if (isset($credentials['http-basic'])) {
            $basic = $credentials['http-basic'];

            if (isset($basic['username'], $basic['password'])) {
                $auth = $this->str->base64Encode($basic['username'].':'.$basic['password']);
                $headers[] = 'Authorization: Basic '.$auth;
            }
        }

        if (isset($credentials['bearer'])) {
            $headers[] = 'Authorization: Bearer '.$credentials['bearer'];
        }

        if (isset($credentials['github-oauth'])) {
            $headers[] = 'Authorization: token '.$credentials['github-oauth'];
        }

        if (isset($credentials['gitlab-oauth'])) {
            $headers[] = 'Authorization: Bearer '.$credentials['gitlab-oauth'];
        }

        if (isset($credentials['gitlab-token'])) {
            $headers[] = 'Private-Token: '.$credentials['gitlab-token'];
        }

        if (isset($credentials['license'])) {
            $headers[] = 'Authentication: License '.$credentials['license'];
        }

        return $headers;
    }

    /**
     * Build modified URL with appended query parameters.
     *
     * @param  string  $url
     * @param  array<string, mixed>  $credentials
     * @return string
     */
    public function buildUrl(string $url, array $credentials): string
    {
        if (empty($credentials['query'])) {
            return $url;
        }

        $separator = $this->str->contains($url, '?') ? '&' : '?';

        return $url.$separator.$this->buildCustomQueryString($credentials['query']);
    }

    /**
     * Build a query string without array indices for simple list arrays.
     */
    protected function buildCustomQueryString(array $query): string
    {
        return $this->str->pregReplace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $this->str->httpBuildQuery($query));
    }
}
