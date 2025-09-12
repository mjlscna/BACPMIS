<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Remove unique from mop_lot_uid only if it exists
        if (Schema::hasColumn('mop_lot', 'mop_lot_uid')) {
            Schema::table('mop_lot', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('mop_lot');
                foreach ($indexes as $index) {
                    if ($index->isUnique() && in_array('mop_lot_uid', $index->getColumns())) {
                        $table->dropUnique($index->getName());
                    }
                }
            });
        }

        // 2. Add uid to mop_item
        Schema::table('mop_item', function (Blueprint $table) {
            if (!Schema::hasColumn('mop_item', 'uid')) {
                $table->string('uid', 150)->nullable()->after('prItemID');
            }
        });
    }

    public function down(): void
    {
        // 1. Re-add unique on mop_lot_uid
        Schema::table('mop_lot', function (Blueprint $table) {
            $table->unique('mop_lot_uid');
        });

        // 2. Drop uid from mop_item
        Schema::table('mop_item', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};

