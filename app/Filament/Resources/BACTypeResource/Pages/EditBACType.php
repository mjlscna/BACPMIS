<?php

namespace App\Filament\Resources\BACTypeResource\Pages;

use App\Filament\Resources\BACTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBACType extends EditRecord
{
    protected static string $resource = BACTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
