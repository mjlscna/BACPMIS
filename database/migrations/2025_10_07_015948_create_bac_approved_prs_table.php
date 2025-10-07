<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // ..._create_bac_approved_prs_table.php

    public function up(): void
    {
        Schema::create('bac_approved_prs', function (Blueprint $table) {
            $table->id();
            $table->string('procID', 50); // Renamed column
            $table->string('filepath');
            $table->text('remarks')->nullable();
            $table->timestamps();

            // The foreign key now uses the 'procID' column in this table
            $table->foreign('procID')
                ->references('procID')->on('procurements')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bac_approved_prs');
    }
};
