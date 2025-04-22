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
        Schema::create('bid_mode_of_procurement', function (Blueprint $table) {
            $table->id();

            // procID as varchar (string)
            $table->string('procID')->index();

            // Foreign key to mode_of_procurements
            $table->foreignId('mode_of_procurement_id')
                ->constrained('mode_of_procurements')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes(); // Optional: Enable soft delete functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bid_mode_of_procurement');
    }
};
