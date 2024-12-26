<?php

use App\Enums\AffiliationEnum;
use App\Enums\ContractClassificationEnum;
use App\Enums\TradeClassification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('minimum_price')->nullable();
            $table->string('maximum_price')->nullable();
            $table->boolean('skill_matching')->default(false);
            $table->string('accept')->nullable();
            $table->boolean('remote_operation_possible')->default(false);
            $table->string('contract_start_date')->nullable();
            $table->string('contract_end_date')->nullable();
            $table->text('experience')->nullable();
            $table->json('languages')->nullable();
            $table->boolean('possible_to_continue')->default(false);
            $table->text('project_description');
            $table->text('personnel_requirement')->nullable();
            $table->boolean('project_finalized')->default(false);
            $table->json('work_location_prefer')->nullable();
            $table->json('scoring')->nullable();
            $table->enum('trade_classification',[
                TradeClassification::End->value,
                TradeClassification::Primary->value,
                TradeClassification::Secondary->value,
//                TradeClassification::Third->value,
            ]);
            $table->enum('contract_classification',[
                ContractClassificationEnum::OUTSOURCING_CONTRACT->value,
//                ContractClassificationEnum::OUTSOURCING->value,
                ContractClassificationEnum::DISPATCH_CONTRACT->value,
            ]);
            $table->enum('status', ['5', '6', '7', '8'])->default('5');
            $table->json('affiliation')->nullable();
            $table->string('deadline')->nullable();
            $table->string('number_of_application')->nullable();
            $table->string('number_of_interviewers')->nullable();
            $table->string("commercial_flow");
            $table->string("person_in_charge");
            $table->boolean("is_public")->default(false);
            $table->boolean("company_info_disclose")->default(false);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("updater_id")->nullable();
            $table->unsignedBigInteger("deleter_id")->nullable();
//            $table->foreign('company_id')->references('id')->on('companies');
//            $table->foreign('user_id')->references('id')->on('users');
//            $table->foreign('updater_id')->references('id')->on('users');
//            $table->foreign('deleter_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('project_save', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('project_sub_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('sub_category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_save');
        Schema::dropIfExists('project_sub_category');
    }
};
