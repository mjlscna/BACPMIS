<?php

namespace App\Filament\Resources\CategoryVenueResource\Pages;

use App\Filament\Resources\CategoryVenueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryVenues extends ListRecords
{
    protected static string $resource = CategoryVenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
