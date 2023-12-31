# This is my package filament-import-excel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jrpikong/filament-import-excel.svg?style=flat-square)](https://packagist.org/packages/jrpikong/filament-import-excel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/jrpikong/filament-import-excel/run-tests?label=tests)](https://github.com/jrpikong/filament-import-excel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/jrpikong/filament-import-excel/Check%20&%20fix%20styling?label=code%20style)](https://github.com/jrpikong/filament-import-excel/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jrpikong/filament-import-excel.svg?style=flat-square)](https://packagist.org/packages/jrpikong/filament-import-excel)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require jrpikong/filament-import-excel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-import-excel-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-import-excel-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-import-excel-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filament-import-excel = new Jrpikong\FilamentImportExcel();
echo $filament-import-excel->echoPhrase('Hello, Jrpikong!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Deny Utama](https://github.com/jrpikong)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
