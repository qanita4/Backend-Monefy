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
    // Fungsi store tetap simpel seperti yang kamu mau
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'belum_terbeli';

        $wishlist = Wishlist::create($validated);

        return response()->json([
            'message' => 'Wishlist berhasil dibuat!',
            'data'    => $wishlist
        ], 201);
    }

    // Fungsi khusus ketika tombol "Beli" di Wishlist ditekan
    public function completePurchase(Request $request, $id)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id', // Harus memilih wallet pengurang
        ]);

        $wishlist = Wishlist::findOrFail($id);

        // Pastikan wishlist ini milik user yang sedang login
        if ($wishlist->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Pastikan barang belum terbeli sebelumnya agar tidak memotong saldo dua kali
        if ($wishlist->status === 'terbeli') {
            return response()->json(['message' => 'Barang ini sudah ditandai terbeli!'], 400);
        }

        $wallet = Wallet::findOrFail($request->wallet_id);

        // Pastikan wallet milik user yang login
        if ($wallet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized Wallet'], 403);
        }

        // Cek apakah saldo wallet cukup
        if ($wallet->balance < $wishlist->target_amount) {
            return response()->json(['message' => "Saldo di dompet {$wallet->name_wallet} tidak cukup untuk membeli wishlist ini!"], 400);
        }

        try {
            DB::transaction(function () use ($wishlist, $wallet) {
                // 1. Potong saldo wallet sebesar nominal harga wishlist
                $wallet->decrement('balance', $wishlist->target_amount);

                // 2. Ubah status wishlist menjadi terbeli
                $wishlist->update([
                    'status' => 'terbeli'
                ]);

                // 3. Otomatis catat ke tabel transactions sebagai pengeluaran (Expense)
                Transaction::create([
                    'user_id'          => Auth::id(),
                    'wallet_id'        => $wallet->id,
                    'name'            => "Membeli Wishlist: " . $wishlist->name,
                    'amount'           => $wishlist->target_amount,
                    'type'             => 'expense',
                    'category'         => 'Belanja', // atau sesuaikan kategori kamu
                    'transaction_date' => now()->toDateString(),
                    'wishlist_id'      => $wishlist->id, // Menyimpan relasi sejarahnya
                    'note'             => 'Pembelian otomatis via menu Wishlist'
                ]);
            });

            return response()->json([
                'message' => 'Wishlist berhasil dibeli dan saldo dompet telah dipotong!',
                'data'    => $wishlist->load('transactions') // opsional jika model ada relasi
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses pembelian: ' . $e->getMessage()], 500);
        }
    }
}