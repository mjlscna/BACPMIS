<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePerItemToProcurementTypeInYourTableName extends Migration
{
    public function up()
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Remove old boolean column
            $table->dropColumn('perItem');

            // Add new string or enum column
            $table->enum('procurement_type', ['perItem', 'perLot'])
                  ->default('perLot')
                  ->after('pr_number');
        });
    }

    public function down()
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Rollback: drop new column
            $table->dropColumn('procurement_type');

            // Restore old boolean column
            $table->boolean('perItem')->default(false)->after('pr_number');
        });
    }
}
