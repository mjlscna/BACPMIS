<?php

namespace App\Filament\Resources\FundClassResource\Pages;

use App\Filament\Resources\FundClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundClass extends EditRecord
{
    protected static string $resource = FundClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
