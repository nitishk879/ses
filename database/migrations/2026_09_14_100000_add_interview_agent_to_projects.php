<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which interview bot conducts this project's screening calls.
 *
 * Held on the project rather than on the candidate because that is the unit a
 * recruiter actually thinks in: "engineering roles get the technical bot,
 * back-office roles get the polite one". A per-candidate override can be added
 * later against this same column; starting per-candidate would mean asking the
 * question once per invitation, which is how a setting ends up unset.
 *
 * A string, not a foreign key: the bot lives in the DenAI dashboard's
 * DocumentDB, not in this database. There is nothing here to reference, and
 * pretending otherwise with a constraint the engine cannot enforce would be
 * worse than a plain column that is documented.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // 24-hex ObjectId today; sized for whatever the dashboard stores next.
            $table->string('interview_agent_id', 64)
                ->nullable()
                ->after('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('interview_agent_id');
        });
    }
};
