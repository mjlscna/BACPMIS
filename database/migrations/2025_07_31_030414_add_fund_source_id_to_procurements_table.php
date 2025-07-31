<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->foreignId('fund_source_id')
                ->nullable()
                ->after('early_procurement')
                ->constrained('fund_sources')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->dropForeign(['fund_source_id']);
            $table->dropColumn('fund_source_id');
        });
    }

};
