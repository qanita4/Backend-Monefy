<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Melihat daftar wishlist milik user
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id())->latest()->get();

        $wishlists->transform(function ($wishlist) {
            $wishlist->status = $this->normalizeStatus($wishlist->status);
            return $wishlist;
        });

        return response()->json(['status' => 'success', 'data' => $wishlists]);
    }

    // Membuat item wishlist baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:belum_terbeli,terbeli',
        ]);

        $wishlist = Wishlist::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'status' => $this->normalizeStatus($request->status),
        ]);

        return response()->json(['status' => 'success', 'data' => $wishlist], 201);
    }

    // Update status wishlist jadi terbeli / belum terbeli
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:belum_terbeli,terbeli',
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())->findOrFail($id);
        $wishlist->update([
            'status' => $request->status,
        ]);

        return response()->json(['status' => 'success', 'data' => $wishlist]);
    }

    private function normalizeStatus(?string $status): string
    {
        return $status === 'terbeli' ? 'terbeli' : 'belum_terbeli';
    }
}