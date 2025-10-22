<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mop_group_pr_item', function (Blueprint $table) {
            $table->foreignId('mop_group_id')->constrained('mop_groups')->onDelete('cascade');

            // This links to your pr_items table using the prItemID string
            $table->string('pr_item_id');
            $table->foreign('pr_item_id')->references('prItemID')->on('pr_items')->onDelete('cascade');

            $table->primary(['mop_group_id', 'pr_item_id'], 'mop_group_pr_item_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mop_group_pr_item');
    }
};
