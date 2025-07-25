<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update existing values to match new ENUM values
        DB::statement("
            UPDATE procurements
            SET abc_50k = CASE
                WHEN abc_50k = 'above_50k' THEN 'above 50k'
                WHEN abc_50k = '50k_or_less' THEN '50k or less'
                ELSE abc_50k
            END
            WHERE abc_50k IN ('above_50k', '50k_or_less')
        ");

        // Change the ENUM definition
        DB::statement("
            ALTER TABLE procurements
            MODIFY abc_50k ENUM('above 50k', '50k or less') NULL
        ");
    }

    public function down(): void
    {
        // Revert values back to old ENUM options
        DB::statement("
            UPDATE procurements
            SET abc_50k = CASE
                WHEN abc_50k = 'above 50k' THEN 'above_50k'
                WHEN abc_50k = '50k or less' THEN '50k_or_less'
                ELSE abc_50k
            END
            WHERE abc_50k IN ('above 50k', '50k or less')
        ");

        // Revert the ENUM definition
        DB::statement("
            ALTER TABLE procurements
            MODIFY abc_50k ENUM('above_50k', '50k_or_less') NULL
        ");
    }
};
