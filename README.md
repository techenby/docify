<picture>
    <source media="(prefers-color-scheme: dark)" srcset="artwork/banner-dark.png">
    <img alt="Banner for Docify" src="artwork/banner-light.png">
</picture>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/techenby/docify.svg?style=flat-square)](https://packagist.org/packages/techenby/docify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tighten/docify/.github/workflows/tests.yml?branch=main&label=tests)](https://github.com/tighten/docify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/techenby/docify.svg?style=flat-square)](https://packagist.org/packages/techenby/docify)

A simple markdown viewer for TALL stack applications

## Installation

You can install the package via composer:

```bash
composer require techenby/docify
```

Then run the install command to generate a docs folder:

```bash
php artisan docify:install
```

Optionally, you can publish the config and Livewire component and docs layout to configure the package for your application:

```bash
php artisan vendor:publish
```

## Usage

By default, Docify is only viewable when your Laravel application is running in the `local` environment.

To allow additional environments, publish the config file and update `environments`:

```php
'environments' => ['local', 'staging'],
```

Set the local editor used by the Edit link with `DOCIFY_EDITOR`. If it is not set, Docify will also check `DEBUGBAR_EDITOR` and `IGNITION_EDITOR` before defaulting to VS Code.

```dotenv
DOCIFY_EDITOR=cursor
```

## Testing

```bash
composer test
```

## Releasing

Please see [RELEASE.md](RELEASE.md) for the release process.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email andy@techenby.com instead of using the issue tracker.

## Credits

- [Andy Swick](https://github.com/techenby)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
