<?php

namespace App\Policies;

use App\Models\Anggaran;
use App\Models\User;

class AnggaranPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Anggaran $anggaran): bool
    {
        return $user->canViewAllUnits() || $user->unit_id === $anggaran->unit_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isBendaharaUnit();
    }

    public function update(User $user, Anggaran $anggaran): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isBendaharaUnit() && $user->unit_id === $anggaran->unit_id && $anggaran->status === 'draft';
    }

    public function delete(User $user, Anggaran $anggaran): bool
    {
        return $this->update($user, $anggaran);
    }

    public function ajukan(User $user, Anggaran $anggaran): bool
    {
        return $this->update($user, $anggaran);
    }

    public function approve(User $user, Anggaran $anggaran): bool
    {
        return $user->isPimpinanYayasan() && $anggaran->status === 'diajukan';
    }
}
