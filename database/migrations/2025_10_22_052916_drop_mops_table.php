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
        // This will drop the table named 'mops'
        Schema::dropIfExists('mops');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't know the original structure,
        // so we'll just create a basic table.
        Schema::create('mops', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
