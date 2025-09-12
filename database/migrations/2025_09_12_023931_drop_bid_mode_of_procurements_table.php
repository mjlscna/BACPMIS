<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('bid_mode_of_procurements');
    }

    public function down(): void
    {
        // Optionally recreate it if you rollback
        Schema::create('bid_mode_of_procurements', function ($table) {
            $table->id();
            $table->unsignedBigInteger('procID');
            $table->uuid('uid')->unique();
            $table->unsignedBigInteger('mode_of_procurement_id');
            $table->integer('mode_order')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};

