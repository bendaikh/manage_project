<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('superadmin')) {
            return view('dashboard.superadmin');
        } elseif ($user->hasRole('admin')) {
            return view('dashboard.admin');
        } elseif ($user->hasRole('manager')) {
            return view('dashboard.manager');
        } else {
            return view('dashboard.agent');
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
