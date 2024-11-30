<?php

namespace App\Policies;

use App\Models\Talent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TalentPolicy
{
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
        return $user->id === $talent->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_employer;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Talent $talent): bool
    {
        return $user->id === $talent->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Talent $talent): bool
    {
        return $user->id === $talent->user_id;
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
