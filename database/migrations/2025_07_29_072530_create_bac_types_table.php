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
        Schema::create('bac_types', function (Blueprint $table) {
            $table->id();
            // e.g., "TWG"
            $table->string('name');
            $table->string('abbreviation')->unique();// e.g., "Technical Working Group"
            $table->string('slug')->unique()->nullable(); // Optional slug
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bac_types');
    }
};
