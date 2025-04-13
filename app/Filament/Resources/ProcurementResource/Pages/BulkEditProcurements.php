<?php

namespace App\Filament\Resources\ProcurementResource\Pages;

use App\Filament\Resources\ProcurementResource;
use App\Models\Division;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Procurement;

class BulkEditProcurements extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ProcurementResource::class;
    protected static string $view = 'filament.pages.bulk-edit-procurements';

    public array $selectedRecordIds = [];

    public function mount()
    {
        $recordIds = request()->query('recordIds', []);

        if (!is_array($recordIds)) {
            $recordIds = explode(',', $recordIds);
        }

        $this->selectedRecordIds = array_filter(array_map('intval', $recordIds));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Procurement::query()->whereIn('id', $this->selectedRecordIds))
            ->columns([
                TextColumn::make('pr_number')
                    ->label('PR Number')
                    ->alignCenter(),

                TextColumn::make('ib_number')
                    ->label('IB Number')
                    ->alignCenter(),

                TextInputColumn::make('np_no')
                    ->label('NP No.')
                    ->alignCenter()
                    ->rules(['required', 'string', 'max:255']),

                TextColumn::make('procurement_program_project')
                    ->label('Procurement Program / Project')
                    ->wrap(),

                TextInputColumn::make('date_receipt_advance')
                    ->label('Date Receipt (Advance Copy)')
                    ->alignCenter()
                    ->type('date'),

                SelectColumn::make('divisions_id')
                    ->label('Division')
                    ->options(Division::pluck('divisions', 'id')->toArray())
                    ->sortable()
                    ->alignCenter(),

                ToggleColumn::make('early_procurement')
                    ->label('Early Procurement')
                    ->sortable()
                    ->alignCenter()
            ])
            ->striped();
    }

    public function getActions(): array
    {
        return [
            Action::make('Close')
                ->button()
                ->color('gray')
                ->action(fn() => $this->redirect(ProcurementResource::getUrl('index'))), // ✅ Redirects back
        ];
    }
}
