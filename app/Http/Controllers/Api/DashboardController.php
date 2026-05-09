<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller{

    public function getSummary()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();   

        return response()->json([
        'user'               => $user,
        'total_balance'      => $user->wallets()->sum('balance'),
        'total_income'       => $user->transactions()->where('type', 'income')->sum('amount'),
        'total_expense'      => $user->transactions()->where('type', 'expense')->sum('amount'),
        'total_transactions' => $user->transactions()->count(),
        ]);
    }

    public function getTransactions(Request $request)
    {
        $user = auth()->user();

        // 1. Tangkap period dari request, default-nya 'day' (biar sinkron sama summary)
        $period = $request->query('period', 'day');

        $query = $user->transactions()->with('wallet:id,name_wallet');

        // 2. Tambahkan logic filter yang sama persis
        if ($period === 'day') {
            $query->whereDate('transaction_date', now()->toDateString());
        } elseif ($period === 'week') {
            $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year);
        } elseif ($period === 'year') {
            $query->whereYear('transaction_date', now()->year);
        }
        // Jika 'all', tidak perlu whereDate/whereYear

        // 3. Eksekusi dengan paginate agar tidak berat
        $transactions = $query->latest()->paginate(15);

        return response()->json($transactions);
    }

}