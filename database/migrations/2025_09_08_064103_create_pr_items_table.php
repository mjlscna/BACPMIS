<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_items', function (Blueprint $table) {
            $table->id();
            $table->string('procID'); // links to procurement.procID
            $table->string('prItemID')->unique(); // e.g., BAC2025-0001-20250908-001
            $table->string('item_no')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_items');
    }
};
