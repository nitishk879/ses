<?php

namespace App\Http\Controllers;

use App\Jobs\ParseUploadedResume;
use App\Models\Talent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Read an uploaded CV and hand back values for the talent form.
 *
 * The registration form asks for a candidate's name, contact details,
 * education, work history, skills and years of experience — every one of
 * which is already written on the CV being attached two fields further down.
 * Somebody was retyping a document they were uploading in the same breath.
 *
 * Two endpoints rather than one, because the work does not fit in a request.
 * {@see store()} takes the upload, queues it and returns a token immediately;
 * {@see show()} is polled until the parse lands. The reasoning is in
 * {@see ParseUploadedResume} — briefly, a parse was measured at 20 seconds for
 * a one-page CV and longer for a real 職務経歴書, against a php-fpm
 * `max_execution_time` of 30 and an nginx read timeout of 60. Synchronously,
 * this feature would not have failed gracefully; it would have 504'd
 * mid-parse.
 *
 * Nothing here writes to the database. This is a *suggestion* service: the
 * form it feeds is still submitted, validated and saved by the ordinary
 * {@see TalentController::store()} path. That matters, because an extraction
 * can be wrong, and the place to catch a wrong extraction is a human looking
 * at a filled-in form — not a record created behind their back.
 */
class TalentResumeParseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // The same permission that governs creating a talent at all. Reading a
        // CV is not a lesser act than filing one: the result contains a real
        // person's name, email and employment history.
        $this->authorize('create', Talent::class);

        $request->validate([
            // Mirrors TalentController::store() exactly. A file accepted here
            // and rejected there would parse successfully and then fail on
            // submit, which reads as the feature being broken.
            'resume' => 'required|file|mimes:pdf,docx,doc|max:2048',
        ]);

        $file = $request->file('resume');
        $token = (string) Str::uuid();

        // Held on the private disk, never the public one, and deleted by the
        // job the moment it has been read.
        $path = $file->store('tmp/resume-autofill', 'local');

        ParseUploadedResume::dispatch(
            $token,
            $path,
            (int) $request->user()->id,
            (string) $file->getClientOriginalName(),
        );

        return response()->json([
            'token' => $token,
            'status' => 'pending',
        ], 202);
    }

    public function show(Request $request, string $token): JsonResponse
    {
        $this->authorize('create', Talent::class);

        $result = Cache::get(ParseUploadedResume::cacheKey($token));

        // A token that is not yours is a token that does not exist. Anything
        // else would let one recruiter collect another's parsed CV by guessing
        // — and the reply carries a named person's contact details.
        if (! is_array($result) || ($result['user_id'] ?? null) !== (int) $request->user()->id) {
            return response()->json(['status' => 'pending'], 200);
        }

        if (($result['status'] ?? null) === 'failed') {
            return response()->json([
                'status' => 'failed',
                'message' => __('talents/registration.autofill_failed'),
            ], 200);
        }

        // Single use. The page has what it asked for, and a parsed CV should
        // not sit in a shared cache for a quarter of an hour after that.
        Cache::forget(ParseUploadedResume::cacheKey($token));

        return response()->json([
            'status' => 'done',
            'fields' => $result['fields'] ?? [],
            'unmapped_skills' => $result['unmapped_skills'] ?? [],
        ]);
    }
}
