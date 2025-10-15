<?php

namespace App\Filament\Resources\ScheduleForProcurementResource\Pages;

use App\Filament\Resources\ScheduleForProcurementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleForProcurements extends ListRecords
{
    protected static string $resource = ScheduleForProcurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
