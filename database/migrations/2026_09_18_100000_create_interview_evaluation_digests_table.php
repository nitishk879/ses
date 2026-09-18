<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One "N candidates evaluated" email, and the evaluations it covers. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_evaluation_digests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            // Who receives it. Nullable because an evaluation can complete for
            // a project whose owner has since been removed, and that must not
            // cascade away the record of the evaluations themselves.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 16)->default('open');

            // When the window shuts. The closing job is dispatched with this
            // delay; the column exists so a second evaluation can tell whether
            // it is joining a live window or needs to open a new one.
            $table->timestamp('closes_at');

            // Written once, at close, by counting the assigned evaluations.
            $table->unsignedInteger('evaluated')->default(0);
            $table->unsignedInteger('recommended')->default(0);

            // The idempotency guard on the email itself. A queue retry after a
            // successful send must not put a second copy in the inbox.
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            // At most one open digest per project.
            $table->unsignedBigInteger('open_key')->nullable();
            $table->unique('open_key', 'interview_digests_one_open_per_project');

            $table->index(['project_id', 'created_at'], 'interview_digests_latest');
        });

        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->foreignId('digest_id')
                ->nullable()
                ->after('interview_attempt_id')
                ->constrained('interview_evaluation_digests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('digest_id');
        });

        Schema::dropIfExists('interview_evaluation_digests');
    }
};
