<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::query()->with(['seller','shipment']);
        if (Auth::user()->hasRole('seller')) {
            $query->where('seller_id', Auth::id());
        }
        $stocks = $query->orderByDesc('created_at')->paginate(15);
        return response()->json($stocks);
    }
} 