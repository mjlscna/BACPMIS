<?php

namespace App\Filament\Resources\VenueSpecificResource\Pages;

use App\Filament\Resources\VenueSpecificResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVenueSpecific extends EditRecord
{
    protected static string $resource = VenueSpecificResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
