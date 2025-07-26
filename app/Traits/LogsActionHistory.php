<?php

namespace App\Traits;

use App\Models\ActionHistory;
use Illuminate\Support\Facades\Auth;

trait LogsActionHistory
{
    /**
     * Log an action to the history table.
     */
    public function logAction(string $title, string $description = null, array $details = null): void
    {
        ActionHistory::create([
            'user_id' => Auth::id() ?? 0,
            'title' => $title,
            'description' => $description,
            'details' => $details,
        ]);
    }
} 