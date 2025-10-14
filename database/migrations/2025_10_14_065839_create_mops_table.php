<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mops', function (Blueprint $table) {
            $table->id();

            // This creates the polymorphic relationship
            $table->unsignedBigInteger('procurable_id');
            $table->string('procurable_type');

            // Your existing columns
            $table->string('uid')->unique();
            $table->foreignId('mode_of_procurement_id')->constrained();
            $table->integer('mode_order')->default(0);

            $table->timestamps();

            // Index for performance
            $table->index(['procurable_id', 'procurable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mops');
    }
};
