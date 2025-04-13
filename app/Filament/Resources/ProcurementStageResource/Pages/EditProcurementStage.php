<?php

namespace App\Filament\Resources\ProcurementStageResource\Pages;

use App\Filament\Resources\ProcurementStageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcurementStage extends EditRecord
{
    protected static string $resource = ProcurementStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
