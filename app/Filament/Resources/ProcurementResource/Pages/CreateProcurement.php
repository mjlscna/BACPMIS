<?php

namespace App\Filament\Resources\ProcurementResource\Pages;

use App\Filament\Resources\ProcurementResource;
use Filament\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateProcurement extends CreateRecord
{
    protected static string $resource = ProcurementResource::class;
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Procurement')
                ->submit('create'),

            Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }


    protected function shouldCreateAnother(): bool
    {
        return false; // Removes "Create & Create Another" button
    }
}
