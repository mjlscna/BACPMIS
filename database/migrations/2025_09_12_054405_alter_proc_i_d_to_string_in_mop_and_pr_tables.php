<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // mop_lot
        Schema::table('mop_lot', function (Blueprint $table) {
            $table->dropForeign('mop_lot_procid_foreign'); // drop by name
        });
        Schema::table('mop_lot', function (Blueprint $table) {
            $table->string('procID')->change();
            $table->index('procID');
        });

        // mop_item
        Schema::table('mop_item', function (Blueprint $table) {
            $table->dropForeign('mop_item_procid_foreign');
        });
        Schema::table('mop_item', function (Blueprint $table) {
            $table->string('procID')->change();
            $table->index('procID');
        });

        // pr_lot_prstage
        Schema::table('pr_lot_prstage', function (Blueprint $table) {
            $table->dropForeign('pr_lot_prstage_procid_foreign');
        });
        Schema::table('pr_lot_prstage', function (Blueprint $table) {
            $table->string('procID')->change();
            $table->index('procID');
        });

        // pr_item_prstage
        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->dropForeign('pr_item_prstage_procid_foreign');
        });
        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->string('procID')->change();
            $table->index('procID');
        });
    }

    public function down(): void
    {
        // mop_lot
        Schema::table('mop_lot', function (Blueprint $table) {
            $table->dropIndex(['procID']);
            $table->unsignedBigInteger('procID')->change();
            $table->foreign('procID')->references('id')->on('procurements')->restrictOnDelete();
        });

        // mop_item
        Schema::table('mop_item', function (Blueprint $table) {
            $table->dropIndex(['procID']);
            $table->unsignedBigInteger('procID')->change();
            $table->foreign('procID')->references('id')->on('procurements')->restrictOnDelete();
        });

        // pr_lot_prstage
        Schema::table('pr_lot_prstage', function (Blueprint $table) {
            $table->dropIndex(['procID']);
            $table->unsignedBigInteger('procID')->change();
            $table->foreign('procID')->references('id')->on('procurements')->restrictOnDelete();
        });

        // pr_item_prstage
        Schema::table('pr_item_prstage', function (Blueprint $table) {
            $table->dropIndex(['procID']);
            $table->unsignedBigInteger('procID')->change();
            $table->foreign('procID')->references('id')->on('procurements')->restrictOnDelete();
        });
    }
};
