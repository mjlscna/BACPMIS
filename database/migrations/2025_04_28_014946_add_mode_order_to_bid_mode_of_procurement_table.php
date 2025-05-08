<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->integer('mode_order')->nullable()->after('mode_of_procurement_id');
        });
    }

    public function down(): void
    {
        Schema::table('bid_mode_of_procurement', function (Blueprint $table) {
            $table->dropColumn('mode_order');
        });
    }
};
