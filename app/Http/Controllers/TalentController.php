<?php

namespace App\Http\Controllers;

use App\Enums\LangEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Talent;
use App\Rules\DialablePhone;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TalentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $talents = Talent::all();

        return view('talents.index', compact('talents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('talents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            // Required, and validated as dialable rather than merely present.
            // An interview invitation promises a phone call; a number that
            // cannot be parsed to E.164 produces a candidate who books a slot
            // for a call nobody can place. See App\Rules\DialablePhone.
            'phone' => ['required', 'string', 'max:32', 'unique:users,phone', new DialablePhone],
            'affiliation' => 'required|int',
            'contract_type' => 'required|max:255',
            'nationality' => 'required|max:255',
            'gender' => 'required|max:255',
            'date_of_birth' => 'required',
            'language' => 'required',
            'address' => 'required|max:255',
            'cover_letter' => 'required|min:64',
            // `docs` was a typo for `docx`, which meant the one modern Word
            // format the parser can actually read was rejected at upload while
            // legacy binary `.doc` — which it cannot read — was let through.
            'resume' => 'required|file|mimes:pdf,docx,doc|max:2048',
            'education' => 'required|min:3',
            'experience' => 'required|min:3',
            // Whole years. Unbounded `required` accepted "500", which then reads
            // on the profile as five centuries of experience.
            'work_experience' => 'required|integer|min:0|max:70',
            'workLocations' => 'array',
            'work_location.*' => 'integer|in:' . implode(',', array_keys(WorkLocationEnum::cases())),
            'subcategory' => 'required|array',
            // A MONTHLY rate (単価), not an annual salary — the column is
            // `min_monthly_price` and the listing card labels it "Monthly Rate".
            //
            // Both were bare `required`, so a reversed range (min above max) or
            // an annual figure typed into a monthly box saved without complaint
            // and went straight into the budget dimension of the match score,
            // where it quietly excludes the candidate from every project.
            // `lte`/`gte` tie the two together so neither can be read alone.
            'min_monthly_price' => 'required|integer|min:0|max:10000000|lte:max_monthly_price',
            'max_monthly_price' => 'required|integer|min:0|max:10000000|gte:min_monthly_price',
            'nearest_station_prefecture' => 'nullable',
            'nearest_station_line' => 'nullable',
            'nearest_station_name' => 'nullable',
            'privacy' => 'required',
            'participation' => 'required',
            'joining_date' => 'required_if:participation,FUTURE|required_if:participation,FROM_DATE',
            'characteristics' => 'nullable|array'
        ]);

        $user = User::updateOrCreate([
            'email' => $validated['email'],
        ], [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'phone' => $validated['phone'],
            'password' => 'password',
            // The email's local part is NOT unique — neha.pal@gmail.com and
            // neha.pal@dcodingx.co.jp both yield "neha.pal" — but the column
            // is, so the second such candidate used to fail on
            // `users_username_unique`. Suffixed until free.
            'username' => $this->availableUsername($validated['email']),
            'date_of_birth' => $validated['date_of_birth'] ?? today()->subYears(18),
            'gender' => $validated['gender'],
            'nationality' => $validated['nationality'],
            'nearest_station_prefecture' => $validated['nearest_station_prefecture'] ?? '',
            'nearest_station_line' => $validated['nearest_station_line'] ?? '',
            'nearest_station_name' => $validated['nearest_station_name'] ?? '',
            'languages' => [$validated['language']],
            'address' => $validated['address'] ?? ''
        ]);

        $user->roles()->attach(3);

        /*
         * Stored after the user exists, and named with the user id.
         *
         * `"{firstname}-{lastname}.pdf"` collides: a second candidate with the
         * same name overwrote the first one's CV on disk, and both records then
         * pointed at one document — the wrong CV would be read, parsed and
         * scored for one of them.
         */
        if ($request->hasFile('resume')) {
            $extension = $request->file('resume')->getClientOriginalExtension();
            $fileNameToStore = "talent-u{$user->id}-{$validated['firstname']}-{$validated['lastname']}.{$extension}";
            $request->file('resume')->storeAs('public/talents/', $fileNameToStore);
        } else {
            $fileNameToStore = 'default-image.jpg';
        }

        $talent = $user->talent()->updateOrCreate([
            'user_id' => $user->id,
        ],
            [
                'affiliation' => $validated['affiliation'],
                'availability' => $validated['participation'],
                'quasi_delegation_possible' => $validated['contract_type'] == 'quasi_delegation_possible',
                'available_for_contract' => $validated['contract_type'] == 'available_for_contract',
                'available_for_dispatch' => $validated['contract_type'] == 'available_for_dispatch',
                'resume' => $fileNameToStore,
                'cover_letter' => $validated['cover_letter'],
                'address' => $validated['address'],
                'qualifications' => $validated['education'],
                'experience_pr' => $validated['experience'],
                // `work_experience` was validated and then dropped — the line
                // that saved it was commented out, and the column it named did
                // not exist — so "years of experience" was asked for on the form
                // and never stored. See the add_experience_years migration.
                'experience_years' => $validated['work_experience'] ?? null,
                'subcategory' => $validated['subcategory'],
                'min_monthly_price' => $validated['min_monthly_price'],
                'max_monthly_price' => $validated['max_monthly_price'],
                'work_location_prefer' => $validated['workLocations'] ?? '', //[1, 2] / [1,3]
                'other_desire_conditions' => $validated['other_desired_location'] ?? '',
                'privacy' => $validated['privacy'],
                'participation' => $validated['participation'],
//            'experience' => $validated['work_experience'] ?? '',
                'joining_date' => $validated['joining_date'] ?? null,
                'characteristics' => $validated['characteristics'] ?? [],
                'company_id' => Auth::user()->company->id
            ]);

        $talent->subcategories()->attach($validated['subcategory']);
        $talent->locations()->attach($request->input(['locations']));

        return redirect()->route('talents.index');
    }

    /**
     * A username derived from the email that is not already taken.
     *
     * `users.username` is unique but the value it was derived from is not: the
     * part before the "@" is shared by anyone with the same handle at a
     * different domain. Rather than fail the whole registration on a collision
     * nobody can see coming, the first free "name", "name-2", "name-3" wins.
     *
     * Bounded, and falls back to something unique by construction — a loop that
     * can spin forever on a busy table is not an improvement on a 500.
     */
    private function availableUsername(string $email): string
    {
        $base = strstr($email, '@', true) ?: $email;
        $base = mb_substr($base, 0, 40);

        if (! User::where('username', $base)->exists()) {
            return $base;
        }

        for ($suffix = 2; $suffix <= 50; $suffix++) {
            $candidate = "{$base}-{$suffix}";

            if (! User::where('username', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base.'-'.Str::random(8);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('talents.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Talent $talent)
    {
        $this->authorize('update', $talent);

        $talent->load('user', 'locations:id', 'subcategories:id');

        return view('talents.edit', compact('talent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Talent $talent)
    {
        $this->authorize('update', $talent);

        /*
         * This method used to validate one field — `phone` — and then write the
         * record's OWN existing values back over itself for everything else:
         *
         *     'firstname' => $talent->user->firstname,   // not $request
         *     'email'     => matched on, never assigned
         *
         * So a recruiter could correct a misspelled name or a wrong email
         * address, press Submit, get "Talent updated successfully", and have
         * nothing change. Measured: sending a new email and a new first name
         * altered neither, while the phone — the one validated field — saved.
         *
         * Silent data loss reported as success is the worst shape a bug can
         * take, so the whole method now works the way `store()` does: validate
         * everything, then assign what was validated.
         */
        $validated = $request->validate([
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            // Unique "except this candidate's own row" — re-saving without
            // touching the field must not collide with itself.
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($talent->user_id),
            ],
            'phone' => [
                'required', 'string', 'max:32',
                Rule::unique('users', 'phone')->ignore($talent->user_id),
                new DialablePhone,
            ],
            'affiliation' => 'required|int',
            'contract_type' => 'nullable|max:255',
            'nationality' => 'required|max:255',
            'gender' => 'required|max:255',
            'date_of_birth' => 'required|date|before:today',
            'language' => 'required',
            'address' => 'required|max:255',
            'cover_letter' => 'required|min:64',
            // Optional here, unlike on create: the candidate already has a CV
            // on file, and requiring a re-upload to fix a phone number is why
            // records go uncorrected.
            'resume' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
            'education' => 'required|min:3',
            'experience' => 'required|min:3',
            'work_experience' => 'nullable|integer|min:0|max:70',
            'workLocations' => 'nullable|array',
            'workLocations.*' => 'integer|in:' . implode(',', array_keys(WorkLocationEnum::cases())),
            'locations' => 'nullable|array',
            'locations.*' => 'integer|exists:locations,id',
            'subcategory' => 'required|array',
            'subcategory.*' => 'integer|exists:sub_categories,id',
            // Same monthly-rate rules as create: a reversed range feeds the
            // budget dimension of the match score and quietly excludes the
            // candidate from every project.
            'min_monthly_price' => 'required|integer|min:0|max:10000000|lte:max_monthly_price',
            'max_monthly_price' => 'required|integer|min:0|max:10000000|gte:min_monthly_price',
            'nearest_station_prefecture' => 'nullable|max:255',
            'nearest_station_line' => 'nullable|max:255',
            'nearest_station_name' => 'nullable|max:255',
            'privacy' => 'required',
            'participation' => 'required',
            'joining_date' => 'nullable|required_if:participation,future|required_if:participation,from_date|date',
            'characteristics' => 'nullable|array',
        ]);

        $user = $talent->user;

        /*
         * Named with the talent id, not just the person's name.
         *
         * `"{firstname}-{lastname}.pdf"` is not unique: a second Kenji Yamamoto
         * silently overwrote the first one's CV on disk, and both records then
         * pointed at one document. The id makes the name collision-proof, and
         * renaming on every save would orphan the old file, so it is stable.
         */
        if ($request->hasFile('resume')) {
            $extension = $request->file('resume')->getClientOriginalExtension();
            $resume = "talent-{$talent->id}-{$validated['firstname']}-{$validated['lastname']}.{$extension}";
            $request->file('resume')->storeAs('public/talents/', $resume);
        } else {
            $resume = $talent->resume;
        }

        /*
         * `update()` on the record we already hold, not `User::updateOrCreate`
         * keyed on the old email — which could not change the email by
         * construction, since the value it matched on was the value it was
         * meant to replace.
         *
         * `password` is deliberately absent. It used to be set to the literal
         * string 'password' on every save, and with the model's `hashed` cast
         * that silently reset the candidate's credentials each time a recruiter
         * corrected a typo. Verified: after one update the original password no
         * longer worked. Editing a profile must not touch authentication.
         *
         * `roles()->sync([3])` is gone for the same reason: a hardcoded id that
         * wiped every other role the account held.
         */
        $user->update([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            /*
             * `username` is deliberately absent.
             *
             * It used to be re-derived here as `strstr($email, '@', true)`,
             * copied from store(). That is wrong twice over: the column is not
             * on this form and is read nowhere in the application, so an email
             * correction silently rewrote an identity handle nobody asked to
             * change — and the derived value is not unique. Two people can hold
             * neha.pal@gmail.com and neha.pal@dcodingx.co.jp, and the second
             * one saved hit `users_username_unique` and 500'd the edit.
             */
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'nationality' => $validated['nationality'],
            'address' => $validated['address'],
            'nearest_station_prefecture' => $validated['nearest_station_prefecture'] ?? '',
            'nearest_station_line' => $validated['nearest_station_line'] ?? '',
            'nearest_station_name' => $validated['nearest_station_name'] ?? '',
            'languages' => [$validated['language']],
        ]);

        /*
         * `$talent->update(...)`, not `$user->talent->updateOrCreate([...,
         * 'company_id' => Auth::user()->company->id], ...)`. That older form
         * matched on the signed-in recruiter's company, so a recruiter from a
         * different company editing this record would not have updated it — it
         * would have INSERTED a second talent row for the same user.
         *
         * `company_id` is not reassigned at all: editing a record must not move
         * a candidate between companies as a side effect of who pressed Save.
         */
        $contractType = $validated['contract_type'] ?? null;

        $talent->update([
            'affiliation' => $validated['affiliation'],
            'availability' => $validated['participation'],
            'quasi_delegation_possible' => $contractType === 'quasi_delegation_possible',
            'available_for_contract' => $contractType === 'available_for_contract',
            'available_for_dispatch' => $contractType === 'available_for_dispatch',
            'resume' => $resume,
            'cover_letter' => $validated['cover_letter'],
            'qualifications' => $validated['education'],
            'experience_pr' => $validated['experience'],
            'experience_years' => $validated['work_experience'] ?? $talent->experience_years,
            'min_monthly_price' => $validated['min_monthly_price'],
            'max_monthly_price' => $validated['max_monthly_price'],
            'work_location_prefer' => $validated['workLocations'] ?? [],
            'privacy' => $validated['privacy'],
            'joining_date' => $validated['joining_date'] ?? null,
            'characteristics' => $validated['characteristics'] ?? [],
        ]);

        /*
         * Unconditional sync, both of them.
         *
         * These were guarded by `!empty($request->input('subcategories'))` —
         * note the plural, a field the form has never posted — so the guard was
         * never true and neither relation was ever synced. Clearing the last
         * skill area or the last preferred location has to persist, which an
         * `if (!empty(...))` around a sync cannot do.
         */
        $talent->subcategories()->sync($validated['subcategory']);
        $talent->locations()->sync($validated['locations'] ?? []);

        return redirect()
            ->route('talents.index')
            ->with([
                'message' => __('talents/registration.talent_updated'),
                'type' => 'success',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Talent $talent)
    {
        // Was unauthorised: any signed-in recruiter could delete any company's
        // candidate by posting to the route.
        $this->authorize('delete', $talent);

        $talent->delete();

        return redirect()
            ->route('talents.index')
            ->with([
                'message' => __('talents/registration.talent_deleted'),
                'type' => 'success',
            ]);
    }
}
