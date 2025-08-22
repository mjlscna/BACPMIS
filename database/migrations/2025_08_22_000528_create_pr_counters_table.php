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
        Schema::create('pr_counters', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary(); // Primary key for each year
            $table->unsignedInteger('last_number')->default(0); // Last used PR number
            $table->timestamps(); // Optional: for audit trail
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_counters');
    }
};
