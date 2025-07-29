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
        Schema::create('bid_mode_of_procurements', function (Blueprint $table) {
            $table->id();
            // make nullable
            $table->foreignId('mode_of_procurement_id')->nullable()->constrained('mode_of_procurements')->nullOnDelete();
            $table->string('procID');
            $table->string('uid');
            $table->integer('mode_order')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('bid_mode_of_procurement');
    }
};
