<?php

use App\Enums\AffiliationEnum;
use App\Enums\ParticipationEnum;
use App\Enums\TalentCharEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('talent', function (Blueprint $table) {
            $table->id();
            $table->text('resume')->nullable();
//            $table->integer('experience')->nullable();
            $table->enum('availability',[
                ParticipationEnum::IMMEDIATELY->value, ParticipationEnum::FUTURE->value, ParticipationEnum::FROM_DATE->value
            ]);
            $table->timestamp('joining_date')->nullable();
            $table->enum('affiliation', [
                AffiliationEnum::COMPANY_EMPLOYEE->value, AffiliationEnum::PARTNER_COMPANY_EMPLOYEE->value, AffiliationEnum::PARTNER_COMPANY_EMPLOYEE_PLUS->value,
                AffiliationEnum::FREELANCER->value
//                , AffiliationEnum::FREELANCER_SINGLE->value, AffiliationEnum::FREELANCER_MORE->value
            ]);

            $table->json('characteristics')->nullable();
            $table->boolean('quasi_delegation_possible')->default(false);
            $table->boolean('available_for_contract')->default(false);
            $table->boolean('available_for_dispatch')->default(false);
            $table->boolean('request_for_contract')->default(false);
            $table->boolean('remote_work_preferred')->default(false);
            $table->json('work_location_prefer')->nullable();
            $table->text('cover_letter')->nullable();
            $table->text('address')->nullable();
            $table->text('experience_pr')->nullable();
            $table->text('qualifications')->nullable();
            $table->integer('min_monthly_price')->nullable();
            $table->integer('max_monthly_price')->nullable();
            $table->text('other_desire_conditions')->nullable();
//            $table->foreign('user_id')->references('id')->on('users');
//            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sub_category_talent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('talent_id');
            $table->unsignedBigInteger('sub_category_id');
            $table->timestamps();
        });

        Schema::create('project_talent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id'); //->constrained()->onDelete('cascade'); // Project reference
            $table->unsignedBigInteger('talent_id'); //->constrained()->onDelete('cascade');  // Talent reference
            $table->enum('status', ['invited', 'hired', 'rejected', 'in_review'])->default('invited'); // Status of the talent in the project
            $table->integer('interview_count')->default(0); // Number of interviews conducted
            $table->text('remarks')->nullable(); // Any additional notes or comments
            $table->timestamps();
        });

        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('talent_id'); //->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('project_id'); //->constrained()->onDelete('cascade');
            $table->dateTime('interview_date');
            $table->boolean('is_scheduled')->default(true); // Flag to check if the schedule is active
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent');
        Schema::dropIfExists('sub_category_talent');
        Schema::dropIfExists('project_talent');
    }
};
