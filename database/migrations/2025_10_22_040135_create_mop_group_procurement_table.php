<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mop_group_procurement', function (Blueprint $table) {
            $table->foreignId('mop_group_id')->constrained('mop_groups')->onDelete('cascade');

            // This links to your procurements table using the procID string
            $table->string('procurement_id');
            $table->foreign('procurement_id')->references('procID')->on('procurements')->onDelete('cascade');

            $table->primary(['mop_group_id', 'procurement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mop_group_procurement');
    }
};
