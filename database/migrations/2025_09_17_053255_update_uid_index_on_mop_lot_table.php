<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mop_lot', function (Blueprint $table) {
            // Drop the existing unique index
            $table->dropUnique('mop_lot_uid_unique'); // name of the unique index in DB

            // (Optional) add a normal index instead
            $table->index('uid');
        });
    }

    public function down(): void
    {
        Schema::table('mop_lot', function (Blueprint $table) {
            // Rollback: remove normal index
            $table->dropIndex(['uid']);

            // Restore unique index
            $table->unique('uid', 'mop_lot_uid_unique');
        });
    }
};

