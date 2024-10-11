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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->unsignedBigInteger('country_id')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('locatable', function (Blueprint $table) {
            $table->id();
            $table->string('location_id');
            $table->string('locatable_type')->default(1);
            $table->unsignedBigInteger('locatable_id')->default(1);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('locatable');
    }
};
