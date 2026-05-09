<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    /**
     * Melihat semua daftar tagihan user.
     */
    public function index()
    {
        // Mengambil tagihan milik user yang sedang login
        $bills = Bill::where('user_id', Auth::id())
                     ->orderBy('due_date', 'asc')
                     ->get();

        return response()->json([
            'status' => 'success',
            'data' => $bills
        ]);
    }

    /**
     * Membuat tagihan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider'       => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0',
            'due_date'       => 'required|date',
            'cycle'          => 'required|string|max:255',
            'status'         => 'nullable|in:unpaid,paid'
        ]);

        $bill = Bill::create([
            'user_id'        => Auth::id(),
            'provider'       => $validated['provider'],
            'account_number' => $validated['account_number'],
            'amount'         => $validated['amount'],
            'due_date'       => $validated['due_date'],
            'cycle'          => $validated['cycle'],
            'status'         => $validated['status'] ?? 'unpaid',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bill created successfully',
            'data'    => $bill
        ], 201);
    }

    /**
     * Menampilkan detail satu tagihan.
     */
    public function show(Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $bill]);
    }

    /**
     * Memperbarui status pembayaran atau data tagihan.
     */
    public function update(Request $request, Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'provider'       => 'sometimes|string|max:255',
            'account_number' => 'sometimes|string|max:255',
            'amount'         => 'sometimes|numeric|min:0',
            'due_date'       => 'sometimes|date',
            'cycle'          => 'sometimes|string|max:255',
            'status'         => 'sometimes|in:unpaid,paid' // Sesuaikan dengan migration (enum)
        ]);

        $bill->update($validated);

        return response()->json(['status' => 'success', 'data' => $bill]);
    }

    /**
     * Menghapus tagihan.
     */
    public function destroy(Bill $bill)
    {
        if ($bill->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bill->delete();

        return response()->json(['status' => 'success', 'message' => 'Bill deleted']);
    }
}