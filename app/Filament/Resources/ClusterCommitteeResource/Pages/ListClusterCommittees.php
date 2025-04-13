<?php

namespace App\Filament\Resources\ClusterCommitteeResource\Pages;

use App\Filament\Resources\ClusterCommitteeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClusterCommittees extends ListRecords
{
    protected static string $resource = ClusterCommitteeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
