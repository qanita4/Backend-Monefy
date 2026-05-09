<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\SavingGoal;
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
            'saving_goal_id'   => 'nullable|exists:saving_goals,id', // Relasi ke tabungan
            'note'             => 'nullable|string',
        ]);

        $wallet = Wallet::findOrFail($validated['wallet_id']);

        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!empty($validated['to_wallet_id'])) {
            $destinationWallet = Wallet::findOrFail($validated['to_wallet_id']);

            if ($destinationWallet->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        if (!empty($validated['saving_goal_id'])) {
            $savingGoal = SavingGoal::findOrFail($validated['saving_goal_id']);

            if ($savingGoal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        // Early Return: Cek Saldo
        if ($validated['type'] !== 'income' && $wallet->balance < $validated['amount']) {
            return response()->json(['message' => 'Saldo tidak cukup!'], 400);
        }

        try {
            $result = DB::transaction(function () use ($validated, $wallet) {
                $validated['user_id'] = Auth::id(); 
                $transaction = Transaction::create($validated);

                // Logika Update Balance Wallet
                if ($validated['type'] === 'income') {
                    $wallet->increment('balance', $validated['amount']);
                } else {
                    $wallet->decrement('balance', $validated['amount']);
                    
                    if ($validated['type'] === 'transfer') {
                        // Jika transfer ke wallet lain
                        if ($validated['to_wallet_id']) {
                            Wallet::findOrFail($validated['to_wallet_id'])->increment('balance', $validated['amount']);
                        }
                        
                        // LOGIKA BARU: Jika transfer ke Saving Goal
                        if ($validated['saving_goal_id']) {
                            $goal = SavingGoal::findOrFail($validated['saving_goal_id']);
                            $goal->increment('current_amount', $validated['amount']);
                            
                            // Update status jika target tercapai
                            if ($goal->current_amount >= $goal->target_amount) {
                                $goal->update(['status' => 'achieved']);
                            }
                        }
                    }
                }

                return $transaction;
            });

            $result->load('wallet:id,name_wallet'); 
            if ($result->type === 'transfer') {
                $result->load('destinationWallet:id,name_wallet');
            }

            return response()->json([
                'message' => 'Transaksi berhasil dicatat!',
                'data'    => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'            => 'nullable|string|max:255',
            'amount'           => 'nullable|numeric|min:0',
            'category'         => 'nullable|string',
            'transaction_date' => 'nullable|date',
            'note'             => 'nullable|string',
            'saving_goal_id'   => 'nullable|exists:saving_goals,id',
        ]);

        if (!empty($validated['saving_goal_id'])) {
            $savingGoal = SavingGoal::findOrFail($validated['saving_goal_id']);

            if ($savingGoal->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        try {
            DB::transaction(function () use ($transaction, $validated) {
                $wallet = $transaction->wallet;
                $oldAmount = (float) $transaction->amount;
                $oldType = $transaction->type;
                $oldGoalId = $transaction->saving_goal_id;

                // 1. REVERSE (Kembalikan saldo ke kondisi semula sebelum diedit)
                if ($oldType === 'income') {
                    $wallet->decrement('balance', $oldAmount);
                } else {
                    $wallet->increment('balance', $oldAmount);
                    if ($oldType === 'transfer') {
                        if ($transaction->to_wallet_id) {
                            Wallet::findOrFail($transaction->to_wallet_id)->decrement('balance', $oldAmount);
                        }
                        if ($oldGoalId) {
                            SavingGoal::findOrFail($oldGoalId)->decrement('current_amount', $oldAmount);
                        }
                    }
                }

                // 2. APPLY (Terapkan nilai baru dari request)
                $newAmount = (float) ($validated['amount'] ?? $oldAmount);
                if ($oldType === 'income') {
                    $wallet->increment('balance', $newAmount);
                } else {
                    $wallet->decrement('balance', $newAmount);
                    if ($oldType === 'transfer') {
                        if ($transaction->to_wallet_id) {
                            Wallet::findOrFail($transaction->to_wallet_id)->increment('balance', $newAmount);
                        }
                        // Jika saving_goal_id berubah atau tetap ada
                        $newGoalId = $validated['saving_goal_id'] ?? $oldGoalId;
                        if ($newGoalId) {
                            $goal = SavingGoal::findOrFail($newGoalId);
                            $goal->increment('current_amount', $newAmount);
                        }
                    }
                }

                $transaction->update($validated);
            });

            return response()->json([
                'message' => 'Transaksi berhasil diperbarui!',
                'data'    => $transaction->load('wallet')
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::transaction(function () use ($transaction) {
                $wallet = $transaction->wallet;
                $amount = (float) $transaction->amount;

                // Reverse balance & saving goal progress
                if ($transaction->type === 'income') {
                    $wallet->decrement('balance', $amount);
                } else {
                    $wallet->increment('balance', $amount);

                    if ($transaction->type === 'transfer') {
                        if ($transaction->to_wallet_id) {
                            Wallet::findOrFail($transaction->to_wallet_id)->decrement('balance', $amount);
                        }
                        if ($transaction->saving_goal_id) {
                            SavingGoal::findOrFail($transaction->saving_goal_id)->decrement('current_amount', $amount);
                        }
                    }
                }

                $transaction->delete();
            });

            return response()->json(['message' => 'Transaksi berhasil dihapus!'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $transactions = $user->transactions()->with(['wallet', 'savingGoal'])->latest()->get();

        return response()->json($transactions);
    }
}