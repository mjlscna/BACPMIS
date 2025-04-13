<?php

namespace App\Filament\Resources\ClusterCommitteeResource\Pages;

use App\Filament\Resources\ClusterCommitteeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClusterCommittee extends EditRecord
{
    protected static string $resource = ClusterCommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
