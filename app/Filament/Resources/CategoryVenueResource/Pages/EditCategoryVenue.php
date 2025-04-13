<?php

namespace App\Filament\Resources\CategoryVenueResource\Pages;

use App\Filament\Resources\CategoryVenueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryVenue extends EditRecord
{
    protected static string $resource = CategoryVenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
