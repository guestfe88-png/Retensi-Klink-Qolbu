<?php

namespace App\Policies;

use App\Models\Berkas;
use App\Models\User;

class BerkasPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Berkas $berkas): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Berkas $berkas): bool
    {
        return true;
    }

    public function delete(User $user, Berkas $berkas): bool
    {
        return $user->isAdmin();
    }

    public function approveDestruction(User $user, Berkas $berkas): bool
    {
        return $user->isAdmin();
    }

    public function requestDestruction(User $user, Berkas $berkas): bool
    {
        return $berkas->status === 'Inaktif' && ! $berkas->legal_hold;
    }

    public function setLegalHold(User $user, Berkas $berkas): bool
    {
        return $user->isAdmin();
    }

    public function export(User $user): bool
    {
        return true;
    }
}
