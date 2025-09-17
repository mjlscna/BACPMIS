<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['name']) && $data['name'] === 'super_admin') {
            $data['permissions'] = Permission::pluck('id')->toArray();
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}
