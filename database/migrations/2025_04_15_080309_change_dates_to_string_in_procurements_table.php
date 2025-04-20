<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->string('immediate_date_needed')->nullable()->change();
            $table->string('date_needed')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->date('immediate_date_needed')->nullable()->change();
            $table->date('date_needed')->nullable()->change();
        });
    }
};

