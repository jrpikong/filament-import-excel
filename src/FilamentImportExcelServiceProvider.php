<?php

namespace Jrpikong\FilamentImportExcel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentImportExcelServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-import-excel';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);
    }
}
