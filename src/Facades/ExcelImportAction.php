<?php

namespace Jrpikong\FilamentImportExcel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 */
class ExcelImportAction extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jrpikong\FilamentImportExcel\ExcelImportAction::class;
    }
}
