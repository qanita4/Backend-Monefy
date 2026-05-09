<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingGoalController extends Controller
{
    // Melihat daftar semua target tabungan
    public function index()
    {
        $goals = SavingGoal::where('user_id', Auth::id())->get();
        
        // Tambahkan perhitungan persentase progres secara dinamis
        $goals->transform(function($goal) {
            $goal->progress_percentage = ($goal->current_amount / $goal->target_amount) * 100;
            return $goal;
        });

        return response()->json(['status' => 'success', 'data' => $goals]);
    }

    // Membuat target tabungan baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'nullable|date',
        ]);

        $goal = SavingGoal::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'target_date' => $request->target_date,
        ]);

        return response()->json(['status' => 'success', 'data' => $goal], 201);
    }
}