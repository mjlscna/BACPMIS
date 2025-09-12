<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mop_lot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procID');
            $table->uuid('uid')->unique();
            $table->unsignedBigInteger('mode_of_procurement_id');
            $table->integer('mode_order')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('procID')
                ->references('id')
                ->on('procurements')
                ->restrictOnDelete();

            $table->foreign('mode_of_procurement_id')
                ->references('id')
                ->on('mode_of_procurements')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mop_lot');
    }
};

