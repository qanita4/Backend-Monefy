<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input sesuai kolom di gambar
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense,transfer',
            'category' => 'required|string',
            'transaction_date' => 'required|date',
            'to_wallet_id' => 'nullable|exists:wallets,id', // Hanya untuk transfer
        ]);

        return DB::transaction(function () use ($request) {
            // 2. Simpan Data Transaksi
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'wallet_id' => $request->wallet_id,
                'to_wallet_id' => $request->to_wallet_id,
                'title' => $request->title,
                'amount' => $request->amount,
                'type' => $request->type,
                'category' => $request->category,
                'note' => $request->note,
                'transaction_date' => $request->transaction_date,
                // attachment akan diurus saat fitur AI Scan siap
            ]);

            // 3. Logika Update Saldo Otomatis
            $wallet = Wallet::findOrFail($request->wallet_id);
            if ($request->type == 'expense' && $wallet->balance < $request->amount) {
            return response()->json(['message' => 'Saldo tidak cukup!'], 400);
}

            if ($request->type == 'expense') {
                $wallet->decrement('balance', $request->amount);
            } 
            elseif ($request->type == 'income') {
                $wallet->increment('balance', $request->amount);
            } 
            elseif ($request->type == 'transfer') {
                // Kurangi saldo pengirim
                $wallet->decrement('balance', $request->amount);
                // Tambah saldo penerima
                $toWallet = Wallet::findOrFail($request->to_wallet_id);
                $toWallet->increment('balance', $request->amount);
            }

            return response()->json([
                'message' => 'Transaksi berhasil dicatat dan saldo terupdate!',
                'data' => $transaction
            ], 201);
        });
    }
}