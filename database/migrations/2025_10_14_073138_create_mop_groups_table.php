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
        Schema::create('mop_groups', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number')->unique()->nullable();
            $table->string('status')->default('draft'); // e.g., draft, bidding, awarded
            // This will be filled in when you save Tab 2
            $table->foreignId('mode_of_procurement_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mop_groups');
    }
};
