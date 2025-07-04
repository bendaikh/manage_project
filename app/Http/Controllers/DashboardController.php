<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        switch ($user->role) {
            case 'admin':
                return redirect()->route('dashboard.admin');
            case 'manager':
                return redirect()->route('dashboard.manager');
            case 'agent':
                return redirect()->route('dashboard.agent');
            default:
                return redirect()->route('dashboard.agent');
        }
    }

    public function admin()
    {
        return view('dashboard.admin');
    }

    public function manager()
    {
        return view('dashboard.manager');
    }

    public function agent()
    {
        return view('dashboard.agent');
    }
}
