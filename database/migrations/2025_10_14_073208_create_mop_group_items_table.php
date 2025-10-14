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
        Schema::create('mop_group_items', function (Blueprint $table) {
            $table->foreignId('mop_group_id')->constrained()->cascadeOnDelete();

            // Manually define the polymorphic columns
            $table->string('biddable_type');
            $table->string('biddable_id');

            // Index for performance
            $table->index(['biddable_id', 'biddable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mop_group_items');
    }
};
