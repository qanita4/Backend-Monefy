<?php 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Carbon\Carbon;

class WalletController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name_wallet' => 'required|string|max:255', // Contoh: "Bank BCA", "Gopay", "Cash"
            'balance' => 'required|numeric|min:0',
        ]);

        $wallet = Wallet::create([
            'user_id' => Auth::id(), // Mengambil ID user yang sedang login
            'name_wallet' => $request->name_wallet,
            'balance' => $request->balance,
        ]);

        return response()->json([
            'message' => 'Dompet berhasil ditambahkan!',
            'data' => $wallet
        ], 201);
    }

    public function getDashboardSummary()
    {
       $userId = Auth::id();
        $now = Carbon::now();

        // 1. Total Saldo (Akumulasi semua wallet)
        $totalBalance = Wallet::where('user_id', $userId)->sum('balance');

        // 2. Total Pemasukan Bulan Ini
        $monthlyIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        // 3. Total Pengeluaran Bulan Ini
        $monthlyExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('amount');

        // 4. Daftar Wallet (untuk list di dashboard)
        $wallets = Wallet::where('user_id', $userId)->get();

        // 5. Transaksi Terakhir (Limit 5)
        $recentTransactions = Transaction::where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_balance' => (float) $totalBalance,
                'monthly_income' => (float) $monthlyIncome,
                'monthly_expense' => (float) $monthlyExpense,
                'wallets' => $wallets,
                'recent_transactions' => $recentTransactions
            ]
        ]);
    }
}