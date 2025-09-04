<?php

namespace App\Http\Controllers;

use App\Models\ActionHistory;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ActionHistory::with('user');
        
        // Date range filtering
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // User filtering
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Title filtering (partial match)
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        
        // Description filtering (partial match)
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        
        $histories = $query->orderByDesc('created_at')->paginate(20);
        
        // Get users for filter dropdown
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return view('history.index', compact('histories', 'users'));
    }
} 