<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if foreign key exists before dropping
        if ($this->hasForeignKey('ntf_bid_schedules', 'ntf_bid_schedules_procid_foreign')) {
            Schema::table('ntf_bid_schedules', function (Blueprint $table) {
                $table->dropForeign(['procID']);
            });
        }

        // Change column type
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            $table->string('procID')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ntf_bid_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('procID')->change();
            $table->foreign('procID')->references('id')->on('procurements');
        });
    }

    /**
     * Helper method to check if a foreign key exists.
     */
    protected function hasForeignKey(string $table, string $foreignKeyName): bool
    {
        $connection = Schema::getConnection()->getDoctrineSchemaManager();
        $doctrineTable = $connection->listTableDetails($table);
        return $doctrineTable->hasForeignKey($foreignKeyName);
    }
};
