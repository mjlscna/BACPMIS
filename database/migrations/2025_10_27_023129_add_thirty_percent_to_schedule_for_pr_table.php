<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schedule_for_pr', function (Blueprint $table) {
            $table->decimal('thirty_percent', 15, 2)->nullable()->after('five_percent');
        });

        // Populate existing records with 30% of ABC
        DB::table('schedule_for_pr')
            ->whereNotNull('ABC')
            ->update([
                'thirty_percent' => DB::raw('(ABC * 0.30)')
            ]);
    }

    public function down(): void
    {
        Schema::table('schedule_for_pr', function (Blueprint $table) {
            $table->dropColumn('thirty_percent');
        });
    }
};

