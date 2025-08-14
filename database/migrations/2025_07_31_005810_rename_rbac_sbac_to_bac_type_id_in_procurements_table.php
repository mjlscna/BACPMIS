<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Rename rbac_sbac to bac_type_id
            if (Schema::hasColumn('procurements', 'rbac_sbac')) {
                $table->renameColumn('rbac_sbac', 'bac_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Rollback: rename bac_type_id back to rbac_sbac
            if (Schema::hasColumn('procurements', 'bac_type_id')) {
                $table->renameColumn('bac_type_id', 'rbac_sbac');
            }
        });
    }
};

