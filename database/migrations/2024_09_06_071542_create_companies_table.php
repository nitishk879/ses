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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string("company_name");
            $table->string("company_email");
            $table->string("industry");
            $table->string("company_logo")->nullable();
            $table->string("company_url")->nullable();
            $table->string("company_location")->nullable();
            $table->string("telephone_number")->nullable();
            $table->string("established");
            $table->integer("number_of_employees");
            $table->integer("capital");
            $table->string("dispatch_business_license")->nullable();
            $table->string("area_of_expertise")->nullable();
            $table->string("specialized_industries")->nullable();
            $table->text("introduction")->nullable();
            $table->boolean("company_information_disclose")->default(false);
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("updater_id")->nullable();
            $table->unsignedBigInteger("deleter_id")->nullable();
//            $table->foreign('user_id')->references('id')->on('users');
//            $table->foreign('updater_id')->references('id')->on('users');
//            $table->foreign('deleter_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });

//        Schema::create('company_favorite_project', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('company_id')->constrained()->onDelete('cascade');
//            $table->foreignId('project_id')->constrained()->onDelete('cascade');
//            $table->timestamps();
//        });
//
//        Schema::create('company_favorite_user', function (Blueprint $table) {
//            $table->id();
//            $table->foreignId('company_id')->constrained()->onDelete('cascade');
//            $table->foreignId('user_id')->constrained()->onDelete('cascade');
//            $table->timestamps();
//        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
//        Schema::dropIfExists('company_favorite_project');
//        Schema::dropIfExists('company_favorite_user');
    }
};
