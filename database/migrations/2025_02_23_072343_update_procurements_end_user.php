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
            // Add the end_users_id field after date_needed
            $table->foreignId('end_users_id')
                ->nullable()
                ->constrained('end_users')
                ->nullOnDelete()
                ->after('date_needed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Remove end_users_id
            $table->dropForeign(['end_users_id']);
            $table->dropColumn('end_users_id');
        });
    }
};
