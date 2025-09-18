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
        Schema::table('pr_items', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->default(0)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('pr_items', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }

};
