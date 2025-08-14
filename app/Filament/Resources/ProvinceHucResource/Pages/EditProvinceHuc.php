<?php

namespace App\Filament\Resources\ProvinceHucResource\Pages;

use App\Filament\Resources\ProvinceHucResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProvinceHuc extends EditRecord
{
    protected static string $resource = ProvinceHucResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
