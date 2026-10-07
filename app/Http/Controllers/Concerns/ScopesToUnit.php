<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ScopesToUnit
{
    /**
     * Unit yang sedang difilter untuk request ini. Bendahara Unit selalu dikunci ke unitnya
     * sendiri; peran tingkat yayasan boleh memilih unit tertentu lewat query `?unit_id=` atau
     * null untuk melihat semua unit.
     */
    protected function resolveUnitId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user->canViewAllUnits()) {
            return $user->unit_id;
        }

        return $request->integer('unit_id') ?: null;
    }

    /**
     * Pastikan Bendahara Unit hanya bisa menulis data ke unitnya sendiri.
     */
    protected function authorizeUnit(Request $request, int $unitId): void
    {
        $user = $request->user();

        abort_unless($user->canViewAllUnits() || $user->unit_id === $unitId, 403);
    }
}
