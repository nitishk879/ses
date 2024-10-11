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
        Schema::create('project_features', function (Blueprint $table) {
            $table->id();
            $table->boolean("recruitment_target_1")->default(false);
            $table->boolean("recruitment_target_2")->default(false);
            $table->boolean("recruitment_target_3")->default(false);
            $table->boolean("recruitment_target_4")->default(false);
            $table->boolean("recruitment_target_5")->default(false);
            $table->boolean("recruitment_target_6")->default(false);
            $table->boolean("case_feature_1")->default(false);
            $table->boolean("case_feature_2")->default(false);
            $table->boolean("case_feature_3")->default(false);
            $table->boolean("case_feature_4")->default(false);
            $table->boolean("case_feature_5")->default(false);
            $table->boolean("case_feature_6")->default(false);
            $table->boolean("case_feature_7")->default(false);
            $table->boolean("case_feature_8")->default(false);
            $table->boolean("case_feature_9")->default(false);
            $table->boolean("case_feature_10")->default(false);
            $table->boolean("case_feature_11")->default(false);
            $table->boolean("case_feature_12")->default(false);
            $table->boolean("case_feature_13")->default(false);
            $table->boolean("case_feature_14")->default(false);
            $table->boolean("case_feature_15")->default(false);
            $table->unsignedBigInteger('project_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_features');
    }
};
