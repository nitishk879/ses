<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Delete CV uploads that were queued for parsing and never collected.
 *
 * {@see \App\Jobs\ParseUploadedResume} deletes its own file in a `finally`, so
 * in normal operation this finds nothing. It exists for the case where the
 * job never ran at all — the queue worker was stopped, the machine was
 * restarted mid-queue, the row was flushed. Without it, every such upload is
 * a stranger's CV left on disk indefinitely, from a form that was very likely
 * abandoned.
 *
 * An hour is generous: the job's own timeout is five minutes, so anything
 * older than that is not "still working", it is orphaned.
 */
class PruneResumeUploads extends Command
{
    protected $signature = 'talents:prune-resume-uploads {--hours=1 : Delete uploads older than this}';

    protected $description = 'Delete abandoned CV uploads left behind by the autofill queue';

    private const DIRECTORY = 'tmp/resume-autofill';

    public function handle(): int
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::DIRECTORY)) {
            return self::SUCCESS;
        }

        $cutoff = Carbon::now()->subHours(max(1, (int) $this->option('hours')));
        $deleted = 0;

        foreach ($disk->files(self::DIRECTORY) as $file) {
            if (Carbon::createFromTimestamp($disk->lastModified($file))->lt($cutoff)) {
                $disk->delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            // Worth a log line rather than silence: a non-zero count here means
            // jobs are being queued and not run, which is a queue problem
            // wearing a disk-usage costume.
            Log::warning('talent.resume_autofill.pruned', ['files' => $deleted]);
            $this->warn("Deleted {$deleted} abandoned CV upload(s).");
        }

        return self::SUCCESS;
    }
}
