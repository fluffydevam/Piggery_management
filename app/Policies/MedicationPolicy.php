<?php

namespace App\Policies;

use App\Models\Medication;
use App\Models\User;

/**
 * The built-in medication catalog (user_id = NULL) is shared by every
 * account, so it must be read-only. Accounts may only change the entries
 * they created themselves.
 */
class MedicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Medication $medication): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Medication $medication): bool
    {
        return $this->owns($user, $medication);
    }

    public function delete(User $user, Medication $medication): bool
    {
        return $this->owns($user, $medication);
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }

    public function restore(User $user, Medication $medication): bool
    {
        return $this->owns($user, $medication);
    }

    public function restoreAny(User $user): bool
    {
        return true;
    }

    public function forceDelete(User $user, Medication $medication): bool
    {
        return $this->owns($user, $medication);
    }

    public function forceDeleteAny(User $user): bool
    {
        return true;
    }

    private function owns(User $user, Medication $medication): bool
    {
        return $medication->user_id !== null
            && (int) $medication->user_id === (int) $user->id;
    }
}
