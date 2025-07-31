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
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('bac_type_id')
                ->nullable()
                ->after('category_type_id')
                ->constrained('bac_types')
                ->nullOnDelete();
        });
    }


};
