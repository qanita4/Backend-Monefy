<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class DashboardController extends Controller{

    public function getSummary()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $totalWallets = $user->wallets()->count();
        $totalTransactions = $user->transactions()->count();
        $totalBalance = $user->wallets()->sum('balance');

        return response()->json([
        'user'               => $user,
        'total_balance'      => $user->wallets()->sum('balance'),
        'total_income'       => $user->transactions()->where('type', 'income')->sum('amount'),
        'total_expense'      => $user->transactions()->where('type', 'expense')->sum('amount'),
        'total_transactions' => $user->transactions()->count(),
        ]);
    }

    public function getTransactions()
    {
        $user = auth()->user();
        $transactions = $user->transactions()->with('wallet')->latest()->get();

        return response()->json($transactions);
    }

}