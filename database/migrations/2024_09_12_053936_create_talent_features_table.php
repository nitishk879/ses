<?php

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
        Schema::create('talent_features', function (Blueprint $table) {
            $table->id();
            $table->boolean('feature_1')->default(false);
            $table->boolean('feature_2')->default(false);
            $table->boolean('feature_3')->default(false);
            $table->boolean('feature_4')->default(false);
            $table->boolean('feature_5')->default(false);
            $table->boolean('feature_6')->default(false);
            $table->boolean('feature_7')->default(false);
            $table->boolean('feature_8')->default(false);
            $table->boolean('feature_9')->default(false);
            $table->boolean('feature_10')->default(false);
            $table->boolean('feature_11')->default(false);
            $table->boolean('feature_12')->default(false);
            $table->unsignedBigInteger('talent_id');
//            $table->foreign('talent_id')->references('id')->on('companies');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent_features');
    }
};
