<?php

namespace App\Filament\Resources\BiddingStatusResource\Pages;

use App\Filament\Resources\BiddingStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBiddingStatuses extends ListRecords
{
    protected static string $resource = BiddingStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
