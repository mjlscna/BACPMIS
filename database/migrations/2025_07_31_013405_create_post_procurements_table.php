<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostProcurementsTable extends Migration
{
    public function up()
    {
        Schema::create('post_procurements', function (Blueprint $table) {
            $table->id();
            $table->string('procID')->index(); // FK to procurement
            $table->date('bid_evaluation_date')->nullable();
            $table->date('post_qual_date')->nullable();
            $table->string('resolution_number')->nullable();
            $table->date('recommending_for_award')->nullable();
            $table->string('award_notice_no')->nullable();
            $table->date('notice_of_award')->nullable();
            $table->decimal('awarded_amount', 15, 2)->nullable();
            $table->string('philgeps_posting_refno')->nullable();// Adjust precision as needed
            $table->date('date_of_posting_of_award_on_philgeps')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('procurement_stage_id')->nullable()->constrained('procurement_stages')->nullOnDelete();
            $table->foreignId('remarks_id')->nullable()->constrained('remarks')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_procurements');
    }
}
