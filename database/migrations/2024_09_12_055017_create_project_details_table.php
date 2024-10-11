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
        Schema::create('project_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("data_category")->nullable();
            $table->unsignedBigInteger("data_medium_section")->nullable();
            $table->unsignedBigInteger("data_sub_section")->nullable();
            $table->string("remarks")->nullable();
            $table->text("text_data")->nullable();
            $table->integer("numerical_data")->nullable();
            $table->timestamp("date_data")->nullable();
            $table->unsignedBigInteger("project_id");
//            $table->foreign("project_id")->references("id")->on("projects")->onDelete("cascade");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};
