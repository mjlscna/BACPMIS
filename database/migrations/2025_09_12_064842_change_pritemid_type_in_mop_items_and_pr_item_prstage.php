<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // First drop foreign key constraint
        Schema::table('mop_item', function (Blueprint $table) {
            $table->dropForeign(['prItemID']); // drops mop_item_pritemid_foreign
        });

        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->dropForeign(['prItemID']);
        });

        // Now change to VARCHAR
        Schema::table('mop_item', function (Blueprint $table) {
            $table->string('prItemID', 150)->change();
        });

        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->string('prItemID', 150)->change();
        });
    }

    public function down(): void
    {
        // Revert column type
        Schema::table('mop_item', function (Blueprint $table) {
            $table->unsignedBigInteger('prItemID')->change();
        });

        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->unsignedBigInteger('prItemID')->change();
        });

        // Re-add foreign keys
        Schema::table('mop_item', function (Blueprint $table) {
            $table->foreign('prItemID')->references('id')->on('pr_items')->onDelete('cascade');
        });

        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->foreign('prItemID')->references('id')->on('pr_items')->onDelete('cascade');
        });
    }
};
