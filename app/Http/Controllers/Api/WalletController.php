<?php 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}