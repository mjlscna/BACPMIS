<?php

namespace App\Filament\Resources\FundSourceResource\Pages;

use App\Filament\Resources\FundSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundSource extends EditRecord
{
    protected static string $resource = FundSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
