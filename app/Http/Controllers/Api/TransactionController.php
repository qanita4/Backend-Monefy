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
        $validated = $request->validate([
            'wallet_id'        => 'required|exists:wallets,id',
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'type'             => 'required|in:income,expense,transfer',
            'category'         => 'required|string',
            'transaction_date' => 'required|date',
            'to_wallet_id'     => 'required_if:type,transfer|nullable|exists:wallets,id',
            'note'             => 'nullable|string',
        ]);

        $wallet = Wallet::findOrFail($validated['wallet_id']);

        // Cek Saldo (Early Return)
        if ($validated['type'] !== 'income' && $wallet->balance < $validated['amount']) {
            return response()->json(['message' => 'Saldo tidak cukup!'], 400);
        }

        try {
            // Kita simpan hasil transaction ke variabel $result
            $result = DB::transaction(function () use ($validated, $wallet) {
                $validated['user_id'] = Auth::id(); 
                $transaction = Transaction::create($validated);

                if ($validated['type'] === 'income') {
                    $wallet->increment('balance', $validated['amount']);
                } else {
                    $wallet->decrement('balance', $validated['amount']);
                    
                    if ($validated['type'] === 'transfer') {
                        Wallet::findOrFail($validated['to_wallet_id'])->increment('balance', $validated['amount']);
                    }
                }

                // Return datanya ke luar closure
                return $transaction;
            });

            $result->load('wallet:id,name_wallet'); 
        
            // Kalau mau narik wallet tujuan juga jika itu transfer:
            if ($result->type === 'transfer') {
                $result->load('destinationWallet:id,name_wallet');
            }

            // Response dikirim di luar closure transaksi
            return response()->json([
                'message' => 'Berhasil!',
                'data'    => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $transactions = $user->transactions()->with('wallet')->latest()->get();

        return response()->json($transactions);
    }
}