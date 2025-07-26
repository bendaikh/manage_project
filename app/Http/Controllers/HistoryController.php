<?php

namespace App\Http\Controllers;

use App\Models\ActionHistory;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = ActionHistory::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('history.index', compact('histories'));
    }
} 