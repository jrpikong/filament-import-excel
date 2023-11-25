<?php

namespace Jrpikong\FilamentImportExcel;

use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class FilamentImportExcelServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-import-excel';

    protected array $resources = [
        // CustomResource::class,
    ];

    protected array $pages = [
        // CustomPage::class,
    ];

    protected array $widgets = [
        // CustomWidget::class,
    ];

    protected array $styles = [
        'plugin-filament-import-excel' => __DIR__ . '/../resources/dist/filament-import-excel.css',
    ];

    protected array $scripts = [
        'plugin-filament-import-excel' => __DIR__ . '/../resources/dist/filament-import-excel.js',
    ];

    // protected array $beforeCoreScripts = [
    //     'plugin-filament-import-excel' => __DIR__ . '/../resources/dist/filament-import-excel.js',
    // ];

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);
    }
}
