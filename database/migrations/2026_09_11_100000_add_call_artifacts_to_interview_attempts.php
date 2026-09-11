<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a call's own artifacts live.
 *
 * Task 13 requires the recording, transcript, Q&A and duration to be stored
 * against the candidate and the job position. Q&A and duration already had
 * homes — `interview_answers` and `interview_attempts.duration_seconds` — but
 * the whole-call recording and the full transcript had nowhere to go except
 * the generic `metadata` JSON blob, which is the wrong place for two reasons:
 * a transcript is not provider metadata, and a column nobody can query is not
 * really storage.
 *
 * `call_sid` is broken out of `provider_reference` because it is the key every
 * poll, every retry and every support question is addressed by, and it needs
 * an index. `provider_reference` stays for whatever a future channel puts
 * there — a Meet event id has no reason to share a column with a Twilio SID.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_attempts', function (Blueprint $table) {
            /*
             * Twilio's CallSid. The AI service polls on this, not on the call
             * config id, because the voicebot's own code warns that the config
             * id is not guaranteed unique per call.
             */
            $table->string('call_sid', 64)->nullable()->after('provider_reference');

            /*
             * The AI service's call-config id. Kept for tracing a call back
             * into the voicebot's `callconfigs` collection.
             */
            $table->string('call_config_id', 64)->nullable()->after('call_sid');

            /*
             * Provider-hosted recording. A URL, never the audio: the file is
             * large, access-controlled at the provider, and copying it here
             * would put candidate audio in our backups.
             */
            $table->string('recording_url', 2048)->nullable()->after('call_config_id');

            /*
             * The full conversation as stored by the voicebot, PII-masked at
             * source. JSON rather than text so turns keep their speaker and
             * timestamp and can be replayed in order.
             */
            $table->json('transcript')->nullable()->after('recording_url');

            /*
             * The classified outcome from the AI service — completed,
             * no_answer, failed, no_transcript. Distinct from `status`, which
             * is our own lifecycle: a call can be `completed` for Twilio and
             * still have produced no screening.
             */
            $table->string('call_outcome', 32)->nullable()->after('transcript');

            /*
             * Polling bookkeeping, so a restarted worker does not start over
             * and a stuck call is visible rather than merely late.
             */
            $table->unsignedSmallInteger('poll_count')->default(0)->after('call_outcome');
            $table->timestamp('last_polled_at')->nullable()->after('poll_count');

            // The lookup every poll does.
            $table->index('call_sid');
        });
    }

    public function down(): void
    {
        Schema::table('interview_attempts', function (Blueprint $table) {
            $table->dropIndex(['call_sid']);
            $table->dropColumn([
                'call_sid',
                'call_config_id',
                'recording_url',
                'transcript',
                'call_outcome',
                'poll_count',
                'last_polled_at',
            ]);
        });
    }
};
