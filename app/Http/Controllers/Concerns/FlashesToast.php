<?php

namespace App\Http\Controllers\Concerns;

use Inertia\Inertia;

trait FlashesToast
{
    protected function toastSuccess(string $message): void
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);
    }

    protected function toastError(string $message): void
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => $message]);
    }
}
