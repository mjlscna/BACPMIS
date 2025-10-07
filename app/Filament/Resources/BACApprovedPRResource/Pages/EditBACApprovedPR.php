<?php

namespace App\Filament\Resources\BACApprovedPRResource\Pages;

use App\Filament\Resources\BACApprovedPRResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBACApprovedPR extends EditRecord
{
    protected static string $resource = BACApprovedPRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
