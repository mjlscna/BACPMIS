<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_b::a::c::type","view_any_b::a::c::type","create_b::a::c::type","update_b::a::c::type","restore_b::a::c::type","restore_any_b::a::c::type","replicate_b::a::c::type","reorder_b::a::c::type","delete_b::a::c::type","delete_any_b::a::c::type","force_delete_b::a::c::type","force_delete_any_b::a::c::type","view_category","view_any_category","create_category","update_category","restore_category","restore_any_category","replicate_category","reorder_category","delete_category","delete_any_category","force_delete_category","force_delete_any_category","view_category::type","view_any_category::type","create_category::type","update_category::type","restore_category::type","restore_any_category::type","replicate_category::type","reorder_category::type","delete_category::type","delete_any_category::type","force_delete_category::type","force_delete_any_category::type","view_cluster::committee","view_any_cluster::committee","create_cluster::committee","update_cluster::committee","restore_cluster::committee","restore_any_cluster::committee","replicate_cluster::committee","reorder_cluster::committee","delete_cluster::committee","delete_any_cluster::committee","force_delete_cluster::committee","force_delete_any_cluster::committee","view_division","view_any_division","create_division","update_division","restore_division","restore_any_division","replicate_division","reorder_division","delete_division","delete_any_division","force_delete_division","force_delete_any_division","view_end::user","view_any_end::user","create_end::user","update_end::user","restore_end::user","restore_any_end::user","replicate_end::user","reorder_end::user","delete_end::user","delete_any_end::user","force_delete_end::user","force_delete_any_end::user","view_fund::class","view_any_fund::class","create_fund::class","update_fund::class","restore_fund::class","restore_any_fund::class","replicate_fund::class","reorder_fund::class","delete_fund::class","delete_any_fund::class","force_delete_fund::class","force_delete_any_fund::class","view_fund::source","view_any_fund::source","create_fund::source","update_fund::source","restore_fund::source","restore_any_fund::source","replicate_fund::source","reorder_fund::source","delete_fund::source","delete_any_fund::source","force_delete_fund::source","force_delete_any_fund::source","view_mode::of::procurement","view_any_mode::of::procurement","create_mode::of::procurement","update_mode::of::procurement","restore_mode::of::procurement","restore_any_mode::of::procurement","replicate_mode::of::procurement","reorder_mode::of::procurement","delete_mode::of::procurement","delete_any_mode::of::procurement","force_delete_mode::of::procurement","force_delete_any_mode::of::procurement","view_procurement","view_any_procurement","create_procurement","update_procurement","restore_procurement","restore_any_procurement","replicate_procurement","reorder_procurement","delete_procurement","delete_any_procurement","force_delete_procurement","force_delete_any_procurement","view_procurement::stage","view_any_procurement::stage","create_procurement::stage","update_procurement::stage","restore_procurement::stage","restore_any_procurement::stage","replicate_procurement::stage","reorder_procurement::stage","delete_procurement::stage","delete_any_procurement::stage","force_delete_procurement::stage","force_delete_any_procurement::stage","view_province","view_any_province","create_province","update_province","restore_province","restore_any_province","replicate_province","reorder_province","delete_province","delete_any_province","force_delete_province","force_delete_any_province","view_province::huc","view_any_province::huc","create_province::huc","update_province::huc","restore_province::huc","restore_any_province::huc","replicate_province::huc","reorder_province::huc","delete_province::huc","delete_any_province::huc","force_delete_province::huc","force_delete_any_province::huc","view_remarks","view_any_remarks","create_remarks","update_remarks","restore_remarks","restore_any_remarks","replicate_remarks","reorder_remarks","delete_remarks","delete_any_remarks","force_delete_remarks","force_delete_any_remarks","view_supplier","view_any_supplier","create_supplier","update_supplier","restore_supplier","restore_any_supplier","replicate_supplier","reorder_supplier","delete_supplier","delete_any_supplier","force_delete_supplier","force_delete_any_supplier","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","view_venue::specific","view_any_venue::specific","create_venue::specific","update_venue::specific","restore_venue::specific","restore_any_venue::specific","replicate_venue::specific","reorder_venue::specific","delete_venue::specific","delete_any_venue::specific","force_delete_venue::specific","force_delete_any_venue::specific","page_BulkEditProcurements"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (!blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (!blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (!blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
