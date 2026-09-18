<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lift the must-have verdict out of the JSON payload into real columns. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_matches', function (Blueprint $table) {
            $table->boolean('meets_mandatory')->default(true)->after('score');

            // Not a boolean: the count is what the screen shows ("2 to check")
            // and what the summary email totals.
            $table->unsignedTinyInteger('unverified_mandatory')->default(0)
                ->after('meets_mandatory');

            // The index behind the default view.
            $table->index(
                ['project_id', 'meets_mandatory', 'score'],
                'ai_matches_gate_ranked'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ai_matches', function (Blueprint $table) {
            $table->dropIndex('ai_matches_gate_ranked');
            $table->dropColumn(['meets_mandatory', 'unverified_mandatory']);
        });
    }
};
