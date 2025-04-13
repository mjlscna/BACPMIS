<?php

namespace App\Filament\Resources\ModeOfProcurementResource\Pages;

use App\Filament\Resources\ModeOfProcurementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModeOfProcurement extends EditRecord
{
    protected static string $resource = ModeOfProcurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
