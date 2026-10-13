<?php

namespace App\Policies;

use App\Models\Lahan;
use App\Models\User;

/**
 * Lahan hanya boleh diubah/dihapus oleh petani pemiliknya.
 */
class LahanPolicy
{
    public function update(User $user, Lahan $lahan): bool
    {
        return $user->petaniProfile !== null && $lahan->petani_profile_id === $user->petaniProfile->id;
    }

    public function delete(User $user, Lahan $lahan): bool
    {
        return $this->update($user, $lahan);
    }
}
