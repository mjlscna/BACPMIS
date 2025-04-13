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
        Schema::table('procurements', function (Blueprint $table) {
            // Drop the old category_venue string field
            $table->dropColumn('category_venue');

            // Add new category_venue foreign key
            $table->foreignId('category_venue_id')->nullable()->constrained('category_venues')->nullOnDelete();

            // Change approved_ppmp from boolean to text
            $table->text('approved_ppmp')->nullable()->change();

            // Change app_updated from boolean to text
            $table->text('app_updated')->nullable()->change();

            // Add remarks_note after remarks_id
            $table->text('remarks_note')->nullable()->after('remarks_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            // Revert category_venue_id change
            $table->dropForeign(['category_venue_id']);
            $table->dropColumn('category_venue_id');

            // Restore category_venue as string
            $table->string('category_venue')->nullable();

            // Convert approved_ppmp back to boolean
            $table->boolean('approved_ppmp')->default(false)->change();

            // Convert app_updated back to boolean
            $table->boolean('app_updated')->default(false)->change();

            // Drop remarks_note
            $table->dropColumn('remarks_note');
        });
    }
};
