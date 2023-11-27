<?php

namespace Jrpikong\FilamentImportExcel\Facades;

use Illuminate\Support\Facades\Facade;

class ExcelImportBudgetPlannerAction extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return \Jrpikong\FilamentImportExcel\ExcelImportBudgetPlannerAction::class;
    }
}
