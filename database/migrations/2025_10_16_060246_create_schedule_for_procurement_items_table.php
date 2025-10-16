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
        Schema::create('schedule_procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_for_procurement_id')->constrained('schedule_for_pr')->onDelete('cascade');

            $table->string('itemable_UID');
            $table->enum('itemable_type', ['perItem', 'perLot']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_procurement_items');
    }
};
