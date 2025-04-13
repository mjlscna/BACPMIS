<?php

namespace App\Filament\Resources\EndUserResource\Pages;

use App\Filament\Resources\EndUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEndUser extends CreateRecord
{
    protected static string $resource = EndUserResource::class;
}
