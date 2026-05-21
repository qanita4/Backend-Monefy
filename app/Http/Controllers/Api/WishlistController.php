<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Melihat semua daftar wishlist user dengan filter status.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');

        $query = Wishlist::where('user_id', Auth::id())->orderBy('created_at', 'desc');

        if (in_array($statusFilter, ['belum_terbeli', 'terbeli'])) {
            $query->where('status', $statusFilter);
        }

        $wishlists = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $wishlists
        ]);
    }

    /**
     * Membuat wishlist baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
            'status'        => 'nullable|in:belum_terbeli,terbeli'
        ]);

        $wishlist = Wishlist::create([
            'user_id'       => Auth::id(),
            'name'          => $validated['name'],
            'target_amount' => $validated['target_amount'],
            'notes'         => $validated['notes'] ?? null,
            'status'        => $validated['status'] ?? 'belum_terbeli',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Wishlist created successfully',
            'data'    => $wishlist
        ], 201);
    }

    /**
     * Menampilkan detail satu wishlist.
     */
    public function show(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => 'success', 
            'data'   => $wishlist
        ]);
    }

    /**
     * Memperbarui data wishlist & eksekusi otomatisasi pemotongan saldo.
     */
    public function update(Request $request, Wishlist $wishlist)
    {
        if ($wishlist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'target_amount' => 'sometimes|numeric|min:0',
            'notes'         => 'sometimes|nullable|string',
            'status'        => 'sometimes|in:belum_terbeli,terbeli',
            'wallet_id'     => 'required_if:status,terbeli|nullable|exists:wallets,id', // Wajib jika status diubah ke 'terbeli'
        ]);

        // LOGIKA UTAMA: Jika status diubah dari 'belum_terbeli' menjadi 'terbeli' (Proses Eksekusi Beli)
        if (isset($validated['status']) && $validated['status'] === 'terbeli' && $wishlist->status === 'belum_terbeli') {
            
            $wallet = Wallet::findOrFail($request->wallet_id);

            // Pastikan wallet milik user yang login
            if ($wallet->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized Wallet'], 403);
            }

            // Cek apakah saldo wallet cukup
            $paymentAmount = $validated['target_amount'] ?? $wishlist->target_amount;
            if ($wallet->balance < $paymentAmount) {
                return response()->json(['message' => "Saldo di dompet {$wallet->name_wallet} tidak cukup untuk membeli wishlist ini!"], 400);
            }

            try {
                DB::transaction(function () use ($wishlist, $wallet, $validated, $paymentAmount) {
                    // 1. Potong saldo wallet sebesar nominal harga wishlist
                    $wallet->decrement('balance', $paymentAmount);

                    // 2. Otomatis catat ke tabel transactions sebagai pengeluaran (Expense)
                    Transaction::create([
                        'user_id'          => Auth::id(),
                        'wallet_id'        => $wallet->id,
                        'title'            => "Membeli Wishlist: " . ($validated['name'] ?? $wishlist->name),
                        'amount'           => $paymentAmount,
                        'type'             => 'expense',
                        'category'         => 'Belanja', 
                        'transaction_date' => now()->toDateString(),
                        'wishlist_id'      => $wishlist->id, 
                        'note'             => 'Pembelian otomatis via menu Wishlist'
                    ]);

                    // 3. Update data wishlist termasuk status menjadi 'terbeli'
                    $wishlist->update($validated);
                });

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Wishlist berhasil dibeli dan saldo dompet telah dipotong!',
                    'data'    => $wishlist->load('transactions')
                ], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal memproses pembelian: ' . $e->getMessage()], 500);
            }
        }

        // Jika hanya update data biasa (bukan eksekusi beli), jalankan update standar seperti BillController
        $wishlist->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Wishlist berhasil diperbarui',
            'data'    => $wishlist
        ], 200);
    }

    /**
     * Menghapus wishlist.
     */
    public function destroy(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $wishlist->delete();

        return response()->json([
            'status'  => 'success', 
            'message' => 'Wishlist deleted'
        ], 200);
    }
}