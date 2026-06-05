<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Wishlist;
use App\Models\Bill;
use Carbon\Carbon;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $userMessage = $request->message;
        $user = Auth::user();

        // Kumpulkan konteks data user
        $totalBalance = Wallet::where('user_id', $user->id)->sum('balance');
        
        $now = Carbon::now();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfYear = Carbon::now()->startOfYear();

        // 1. STATS HARIAN (Today)
        $todayIncome = Transaction::where('user_id', $user->id)->where('type', 'income')->whereDate('transaction_date', $today)->sum('amount');
        $todayExpense = Transaction::where('user_id', $user->id)->where('type', 'expense')->whereDate('transaction_date', $today)->sum('amount');
        $todayTransactions = Transaction::where('user_id', $user->id)->whereDate('transaction_date', $today)->get(['title', 'category', 'amount', 'type']);

        // 2. STATS MINGGUAN (Weekly)
        $weeklyIncome = Transaction::where('user_id', $user->id)->where('type', 'income')->whereBetween('transaction_date', [$startOfWeek, $now])->sum('amount');
        $weeklyExpense = Transaction::where('user_id', $user->id)->where('type', 'expense')->whereBetween('transaction_date', [$startOfWeek, $now])->sum('amount');

        // 3. STATS BULANAN (Monthly)
        $monthlyIncome = Transaction::where('user_id', $user->id)->where('type', 'income')->whereMonth('transaction_date', $now->month)->whereYear('transaction_date', $now->year)->sum('amount');
        $monthlyExpense = Transaction::where('user_id', $user->id)->where('type', 'expense')->whereMonth('transaction_date', $now->month)->whereYear('transaction_date', $now->year)->sum('amount');

        // 4. STATS TAHUNAN (Yearly)
        $yearlyIncome = Transaction::where('user_id', $user->id)->where('type', 'income')->whereYear('transaction_date', $now->year)->sum('amount');
        $yearlyExpense = Transaction::where('user_id', $user->id)->where('type', 'expense')->whereYear('transaction_date', $now->year)->sum('amount');

        // 5. STATS ALL-TIME
        $allIncome = Transaction::where('user_id', $user->id)->where('type', 'income')->sum('amount');
        $allExpense = Transaction::where('user_id', $user->id)->where('type', 'expense')->sum('amount');

        // 6. KATEGORI TERBANYAK BULAN INI
        $categoryBreakdown = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', $now->month)
            ->select('category', \DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // 7. TRANSAKSI TERAKHIR (Recent Transactions)
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderByDesc('transaction_date')
            ->take(10)
            ->get(['title', 'category', 'amount', 'type', 'transaction_date']);

        $wishlists = Wishlist::where('user_id', $user->id)->where('status', 'belum_terbeli')->get(['name', 'target_amount']);
        $bills = Bill::where('user_id', $user->id)->where('status', 'unpaid')->get(['provider', 'amount', 'due_date']);

        $context = "Saya adalah asisten keuangan AI bernama Monefy AI.
Data keuangan pengguna saat ini (" . $now->format('Y-m-d H:i:s') . "):
- Saldo total (semua dompet): Rp " . number_format($totalBalance, 0, ',', '.') . "

1. DATA TRANSAKSI HARI INI:
   - Pemasukan: Rp " . number_format($todayIncome, 0, ',', '.') . "
   - Pengeluaran: Rp " . number_format($todayExpense, 0, ',', '.') . "
   - Detail transaksi hari ini: " . json_encode($todayTransactions) . "

2. DATA TRANSAKSI MINGGU INI (Senin s/d hari ini):
   - Pemasukan: Rp " . number_format($weeklyIncome, 0, ',', '.') . "
   - Pengeluaran: Rp " . number_format($weeklyExpense, 0, ',', '.') . "

3. DATA TRANSAKSI BULAN INI (" . $now->format('F Y') . "):
   - Pemasukan: Rp " . number_format($monthlyIncome, 0, ',', '.') . "
   - Pengeluaran: Rp " . number_format($monthlyExpense, 0, ',', '.') . "

4. DATA TRANSAKSI TAHUN INI (" . $now->year . "):
   - Pemasukan: Rp " . number_format($yearlyIncome, 0, ',', '.') . "
   - Pengeluaran: Rp " . number_format($yearlyExpense, 0, ',', '.') . "

5. TOTAL SEMUA WAKTU (All-time):
   - Total Pemasukan: Rp " . number_format($allIncome, 0, ',', '.') . "
   - Total Pengeluaran: Rp " . number_format($allExpense, 0, ',', '.') . "

6. PENGELUARAN PER KATEGORI BULAN INI:
   " . json_encode($categoryBreakdown) . "

7. 10 TRANSAKSI TERBARU (Terakhir dilakukan):
   " . json_encode($recentTransactions) . "

8. WISHLIST PENGGUNA (Belum terbeli):
   " . json_encode($wishlists) . "

9. TAGIHAN PENGGUNA (Belum dibayar):
   " . json_encode($bills) . "

Pertanyaan pengguna: '$userMessage'

Berikan jawaban yang ramah, ringkas, menggunakan bahasa Indonesia, seperti seorang penasihat keuangan profesional tapi santai. Gunakan emoji untuk membuat percakapan lebih hidup. Jangan berikan jawaban terlalu panjang, berikan saran praktis jika ditanya. Jawab dengan data asli yang telah disediakan di atas. Jika ditanya pengeluaran hari ini/minggu ini/tahun ini/total semua waktu, sebutkan angka rincinya dari data di atas secara akurat!";

        $apiKey = trim(env('GEMINI_API_KEY'));
        
        if (!$apiKey) {
            return response()->json([
                'reply' => "Halo! Saya Monefy AI 🤖. Namun saat ini **API Key Gemini belum dipasang** di sistem oleh developer (di file `.env`), jadi saya belum bisa memberikan jawaban asli. Silakan tambahkan `GEMINI_API_KEY` ya!"
            ]);
        }

        try {
            // Menggunakan model Gemini 2.5 Flash yang terdaftar di akun Anda
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'text' => $context
                                ]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cari text reply dengan safe navigation (null coalescing)
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($reply) {
                    return response()->json(['reply' => $reply]);
                }
                
                return response()->json(['reply' => "Hmm, saya tidak bisa memberikan jawaban untuk itu saat ini. Coba tanyakan hal lain ya!"]);
            }

            // Jika gagal, return status code aslinya agar lebih mudah didebug
            return response()->json([
                'reply' => 'Maaf, terjadi kesalahan saat menghubungi server AI. Detail: ' . $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json(['reply' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}