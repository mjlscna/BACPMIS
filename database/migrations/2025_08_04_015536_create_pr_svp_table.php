<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pr_svps', function (Blueprint $table) {
            $table->id();

            // Foreign keys (adjust types and names as needed)
            $table->string('procID')->nullable();
            $table->string('uid')->nullable();
            $table->string('rfq_no')->nullable();
            $table->date('canvass_date')->nullable();
            $table->date('date_returned_of_canvass')->nullable();
            $table->date('abstract_of_canvass_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_svps');
    }
};
