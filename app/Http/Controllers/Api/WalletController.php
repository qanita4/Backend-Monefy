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
            'name_wallet' => 'required|string|max:255', 
            'balance' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
        ]);

        $wallet = Wallet::create([
            'user_id' => Auth::id(), 
            'name_wallet' => $request->name_wallet,
            'balance' => $request->balance,
            'category' => $request->category ?? 'Default',
        ]);

        return response()->json([
            'message' => 'Dompet berhasil ditambahkan!',
            'data' => $wallet
        ], 201);
    }

    public function index()
    {
        // Mengambil semua wallet aktif milik user yang sedang login
        $wallets = Wallet::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'success',
            'data' => $wallets
        ], 200);
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

    public function update(Request $request, $id)
    {
        $wallet = Wallet::findOrFail($id);

        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name_wallet' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
        ]);

        $wallet->update($request->only('name_wallet', 'balance', 'category'));

        return response()->json([
            'message' => 'wallet berhasil diperbarui',
            'data' => $wallet
        ]);
    }

    public function destroy($id)
    {
        $wallet = Wallet::findOrFail($id);

        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $wallet->delete();

        return response()->json([
            'message' => 'wallet berhasil dihapus'
        ], 200);
    }
}