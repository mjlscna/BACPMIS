<?php

namespace App\Filament\Resources\RemarksResource\Pages;

use App\Filament\Resources\RemarksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRemarks extends EditRecord
{
    protected static string $resource = RemarksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
