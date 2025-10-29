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
        Schema::create('mops', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship columns
            // These link to either Procurement (procID) or PrItem (prItemID)
            $table->string('procurable_type'); // Stores the model class name (e.g., App\Models\Procurement)
            $table->string('procurable_id');   // Stores the ID (procID or prItemID)

            // Foreign key for the ModeOfProcurement details
            $table->foreignId('mode_of_procurement_id')->constrained('mode_of_procurements')->cascadeOnDelete();

            $table->unsignedInteger('mode_order')->default(1); // Order of this mode in the sequence
            $table->string('uid')->unique(); // Unique identifier for this MOP instance

            $table->timestamps();

            // Add index for faster lookups on the polymorphic columns
            $table->index(['procurable_type', 'procurable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mops');
    }
};
