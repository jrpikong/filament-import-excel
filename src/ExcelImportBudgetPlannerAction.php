<?php

namespace Jrpikong\FilamentImportExcel;

use App\Models\Branch;
use App\Models\BudgetPlanner;
use App\Models\ExpenseBudget;
use App\Models\HistoryExpenseBudget;
use App\Services\GenerateCode;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;

class ExcelImportBudgetPlannerAction extends Action
{
    /**
     * @var string
     */
    protected string $importClass = DefaultImport::class;

    /**
     * @var array
     */
    protected array $importClassAttributes = [];

    /**
     * @var string|null
     */
    protected ?string $disk = null;

    /**
     * @param string|null $class
     * @param ...$attributes
     * @return $this
     */
    public function use(string $class = null, ...$attributes): static
    {
        $this->importClass = $class ?: DefaultImport::class;
        $this->importClassAttributes = $attributes;

        return $this;
    }

    /**
     * @return Repository|Application|\Illuminate\Foundation\Application|mixed|string
     */
    protected function getDisk()
    {
        return $this->disk ?: config('filesystems.default');
    }

    /**
     * @return string|null
     */
    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    /**
     * @param Closure|string|null $action
     * @return $this
     * @throws \Exception
     */
    public function action(Closure|string|null $action): static
    {
        if ($action !== 'importData') {
            throw new \Exception('You\'re unable to override the action for this plugin');
        }

        $this->action = $this->importData();

        return $this;
    }

    /**
     * @return array
     */
    protected function getDefaultForm(): array
    {
        return [
            TextInput::make('code')
                ->required()
                ->readOnly()
                ->live()
                ->maxLength(255),
            Select::make('cluster_id')
                ->relationship('cluster', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('expense_budget_id', null);
                    $set('branch_id', null);
                })
                ->required(),
            Select::make('expense_budget_id')
                ->relationship('expenseBudget', 'code', fn($query, $get) => $query->where('cluster_id', $get('cluster_id'))->where('status', '=', 1))
                ->searchable()
                ->live()
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->code} / {$record->budgetType->code} / {$record->period->name} / " . number_format($record->amount))
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    $expense = ExpenseBudget::query()->with(['budgetType', 'cluster'])->find($state);
                    $prefix = 'BPL/' . $expense->budgetType->code . '/' . $expense->cluster->name;
                    $set('code', GenerateCode::create(BudgetPlanner::class, 'code', $prefix));
                })
                ->preload()
                ->required(),
            Select::make('branch_id')
                ->live()
                ->relationship('branch', 'name', fn($query, $get) => $query->where('cluster_id', $get('cluster_id')))
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                    $branchName = Branch::query()->find($state);
                    $expense = ExpenseBudget::query()->with(['budgetType'])->find($get('expense_budget_id'));
                    $prefix = 'BPL/' . $expense->budgetType->code . '/' . $branchName->name;
                    $set('code', GenerateCode::create(BudgetPlanner::class, 'code', $prefix));
                })
                ->searchable()
                ->preload(),
            TextInput::make('total_amount')
                ->label('Amount')
                ->required()
                ->disabledOn(['edit', 'view'])
                ->readOnly(fn(Get $get): bool => !$get('expense_budget_id'))
                ->live()
                ->currencyMask()
                ->hint(function (TextInput $component, string|null $state, Get $get, string $operation) {
                    $hint = '';
                    if ($state && $get('expense_budget_id')) {
                        $amount = ExpenseBudget::query()->find($get('expense_budget_id'))->amount;
                        if ($state > $amount) {
                            $hint = "Amount Can't More Than Value Expense Budget Allocation " . number_format($amount);
                        }
                    }
                    return $hint;
                })
                ->hintColor('danger'),
            Toggle::make('status')
                ->default(1)
                ->required(),
            Fieldset::make('Date')
                ->schema([
                    DatePicker::make('start')
                        ->required(),
                    DatePicker::make('end')
                        ->required(),
                ]),

            FileUpload::make('upload')
                ->label(fn($livewire) => str($livewire->getTable()->getPluralModelLabel())->title() . ' ' . __('Excel Data'))
                ->default(1)
//                ->disk($this->getDisk())
                ->disk('s3')
                ->visibility('private')
                ->columns()
                ->required(),
        ];
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-arrow-down-tray')
            ->color('warning')
            ->form($this->getDefaultForm())
            ->modalIcon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->modalWidth('md')
            ->modalAlignment('center')
            ->modalHeading(fn($livewire) => __('Import Excel'))
            ->modalDescription(__('Import data into database from excel file'))
            ->modalFooterActionsAlignment('right')
            ->closeModalByClickingAway(false)
            ->action('importData');
    }

    /**
     * Import data function.
     * @return Closure
     */
    private function importData(): Closure
    {
        return function (array $data, $livewire): bool {
            $expenseBudget = ExpenseBudget::query()->find($data['expense_budget_id']);
            if ($data['total_amount'] > $expenseBudget->amount) {
                Notification::make()
                    ->danger()
                    ->title('Error')
                    ->body("Amount Can't More Than Value Expense Budget Allocation " . number_format($expenseBudget->amount))
                    ->send();
                $this->halt();
            }
            DB::beginTransaction();
            try {
                $planner = new BudgetPlanner();
                $planner->code = $data['code'];
                $planner->cluster_id = $data['cluster_id'];
                $planner->expense_budget_id = $data['expense_budget_id'];
                $planner->branch_id = $data['branch_id'];
                $planner->total_amount = $data['total_amount'];
                $planner->start = $data['start'];
                $planner->end = $data['end'];
                $planner->status = $data['status'];
                $planner->save();

                $newAmount = $expenseBudget->amount - $data['total_amount'];
                HistoryExpenseBudget::query()->create([
                    'expense_budget_id' => $expenseBudget->id,
                    'user_id' => auth()->user()->id,
                    'before_amount' => $expenseBudget->amount,
                    'amount' => $data['total_amount'],
                    'after_amount' => $newAmount,
                    'references' => null,
                    'entry' => 'OUT',
                    'status' => 'DISTRIBUTE'
                ]);

                $expenseBudget->amount = $newAmount;
                $expenseBudget->save();
                $importObject = new $this->importClass($livewire->getModel(), $planner);
                Excel::import($importObject, $data['upload']);
                DB::commit();
                return true;
            } catch (Exception $exception) {
                Notification::make()
                    ->danger()
                    ->title('Error')
                    ->body($exception->getMessage())
                    ->send();
                $this->halt();
            }
        };
    }
}
