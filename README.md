# Laragear MultiAuth

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laragear/multi-auth.svg)](https://packagist.org/packages/laragear/multi-auth)
[![Latest stable test run](https://github.com/Laragear/MultiAuth/actions/workflows/test.yml/badge.svg)](https://github.com/Laragear/MultiAuth/actions/workflows/test.yml)
[![Codecov coverage](https://codecov.io/gh/Laragear/MultiAuth/graph/badge.svg?token=VNs5caOOpI)](https://codecov.io/gh/Laragear/MultiAuth)
[![Maintainability](https://qlty.sh/gh/Laragear/projects/MultiAuth/maintainability.svg)](https://qlty.sh/gh/Laragear/projects/MultiAuth)

Authenticate multiple Composer packages from the same host using different credentials.

```bash
composer multi-auth:config laragear/web-checkout license ABC-123
```

## Why this package?

Composer natively associates authentication credentials (like HTTP Basic, Bearer tokens, GitHub OAuth) with a single **domain** inside the `auth.json` file. If you have a private package registry serving multiple packages under the same domain (e.g. `keygen.sh` or `github.com`), you cannot easily use different credentials for different packages.

This plugin allows you to define authentication credentials **per-package**, overriding the default domain-based authentication, plus two useful authentication mechanisms:

```json
{
    "http-basic": {
        "...": "..."
    },
    "laragear/web-checkout": {
        "license": "$WEBCHECKOUT_LICENSE"
    }
}
```

## Become a sponsor

[![](.github/assets/support.png)](https://github.com/sponsors/DarkGhostHunter)

Your support allows me to keep this package free, up-to-date, and maintainable. Alternatively, you can **[spread the word!](http://x.com/share?text=I%20am%20using%20this%20cool%20PHP%20package&url=https://github.com%2FLaragear%2FMultiAuth&hashtags=PHP,Composer)**

## Requirements

* PHP 8.3 or later.
* Composer 2.0 or later.

## Installation

Require this package globally or into your project using Composer:

```bash
composer require global laragear/multi-auth
```

> [!IMPORTANT]
> 
> If you require this in your project, the plugin will be active only for a single Composer project.

## Usage

Instead of manually editing your `auth.json` file, use the provided `multi-auth:config` command. The command accepts the package name, the authentication type, and the required credentials:

### Authentication Types

The following authentication types are supported:

#### HTTP Basic

Standard HTTP Basic authentication.

```bash
composer multi-auth:config vendor/package http-basic username password
```

#### Bearer Token

Standard Authorization Bearer token.

```bash
composer multi-auth:config vendor/package bearer my-secret-token
```

#### GitHub / GitLab

Token-based authentication for GitHub or Gitlab. 

```bash
composer multi-auth:config vendor/package github-oauth my-github-token
composer multi-auth:config vendor/package gitlab-oauth my-gitlab-token
composer multi-auth:config vendor/package gitlab-token my-gitlab-private-token
```

#### License Key

Adds a custom `Authentication: License <KEY>` header.

```bash
composer multi-auth:config vendor/package license ABC-123-XYZ
```

#### Custom Query Parameters

You can append custom query parameters to the package download URL. This is especially useful for passing tokens as URL parameters.

```bash
composer multi-auth:config vendor/package query auth "license:\$TOKEN"
```

This will automatically append `?auth=license:{TOKEN}` to the URL when Composer downloads `vendor/package`, where `{TOKEN}` is automatically resolved from your environment variables.

### Global Configuration

If you want to apply the configuration globally across all your Composer projects, append the `--global` (or `-g`) flag.

```bash
composer multi-auth:config vendor/package license ABC-123 --global
```

> [!NOTE]
> 
> This will only work if Multiauth was [installed globally](#installation).

## Environment Variables

You can use environment variables in any of your credentials too! Prefix the variable name with a `$` sign. Append `\` to avoid the shell replacing the variable name with an environment value.

```bash
composer multi-auth:config vendor/package bearer \$MY_SECRET_TOKEN
```

When Composer downloads the package, the plugin will automatically resolve `$MY_SECRET_TOKEN` from your current environment.

## Security

If you discover any security-related issues, please email darkghosthunter@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
