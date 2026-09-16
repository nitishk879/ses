<?php

namespace App\Policies;

use App\Models\Talent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TalentPolicy
{

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return  $user->roles->count() > 0 && !$user->hasRole('talent');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Talent $talent): bool
    {
        return $user !== null && $this->owns($user, $talent);
    }

    /**
     * Who may act on a talent record.
     *
     * Two kinds of people, and the old `$user->id === $talent->user_id` only
     * described the first:
     *
     *  * the candidate themselves, editing their own profile;
     *  * the recruiter whose company the record belongs to — which is how every
     *    talent is actually created (`store()` stamps `company_id` from the
     *    signed-in recruiter). Under the old rule a recruiter failed their own
     *    records, so nothing could be authorised and the checks were simply
     *    left out of the controller instead.
     *
     * Admins never reach here; `before()` answers for them.
     */
    private function owns(User $user, Talent $talent): bool
    {
        if ($user->id === $talent->user_id) {
            return true;
        }

        // `company` is a HasOne, so a recruiter without one owns nothing —
        // and `null === null` must not read as a match.
        $company = $user->company;

        return $company !== null
            && $talent->company_id !== null
            && (int) $company->id === (int) $talent->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        /*
         * Was `return $user->is_employer;` — an attribute that does not exist.
         * `User` declares no such column and no such cast (`isEmployerCast` is
         * written but never registered anywhere), so the expression always
         * evaluated to null and this `bool` return type threw a TypeError.
         *
         * It went unnoticed because `before()` short-circuits for admins and
         * never reaches here, and the only admin is the person who tests. For
         * everyone else, `@can('create', Talent::class)` in the site header
         * meant a 500 on every page that renders a header.
         *
         * Restoring the intent rather than the expression: an employer is a
         * user who has a company. Deliberately not "a company that already has
         * talents", which the unused cast checked — that is circular, since
         * adding the first talent is exactly what this permission is for.
         */
        return $user->company()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Talent $talent): bool
    {
        return $this->owns($user, $talent);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Talent $talent): bool
    {
        return $this->owns($user, $talent);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Talent $talent): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Talent $talent): bool
    {
        //
    }
}
