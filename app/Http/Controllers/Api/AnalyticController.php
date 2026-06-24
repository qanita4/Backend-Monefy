<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticController extends Controller
{
    public function getSummary(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $trend = $request->query('trend', 'weekly');

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);
        $week  = (int) $request->query('week', 1);

        $now = now()->setYear($year)->setMonth($month);

        $incomeQuery = $user->transactions()->where('type', 'income');
        $expenseQuery = $user->transactions()->where('type', 'expense');

        // Filter tanggal di level database (Sama seperti logika awalmu)
        if ($trend === 'weekly') {
            $firstDayOfMonth = $now->copy()->startOfMonth();

            $start = $firstDayOfMonth->copy()->addDays(($week - 1) * 7);
            $end = $start->copy()->addDays(6);

            $incomeQuery->whereBetween('transaction_date', [$start, $end]);
            $expenseQuery->whereBetween('transaction_date', [$start, $end]);
        } elseif ($trend === 'monthly') {
            $incomeQuery->whereMonth('transaction_date', $month)
                        ->whereYear('transaction_date', $year);
            $expenseQuery->whereMonth('transaction_date', $month)
                        ->whereYear('transaction_date', $year);
        } elseif ($trend === 'yearly') {
            $incomeQuery->whereYear('transaction_date', $year);
            $expenseQuery->whereYear('transaction_date', $year);
        }

        // Ambil totalan tren langsung dari database (Cepat & Ringan)
        $totalIncome = (clone $incomeQuery)->sum('amount') ?: 0;
        $totalExpense = (clone $expenseQuery)->sum('amount') ?: 0;
        $totalBalance = $user->wallets()->sum('balance') ?: 0;

        // Deteksi Driver Database (MySQL / PostgreSQL / SQLite) untuk format tanggal yang tepat
        $driver = DB::connection()->getDriverName();
        $dateFormat = $this->getDateFormatSql($driver, $trend);

        // Agregasi GROUP BY langsung di Database (Hanya menarik baris yang dibutuhkan untuk chart)
        $incomeChartRaw = $incomeQuery->select(DB::raw("$dateFormat as date_key"), DB::raw('SUM(amount) as total'))
            ->groupBy('date_key')->pluck('total', 'date_key');

        $expenseChartRaw = $expenseQuery->select(DB::raw("$dateFormat as date_key"), DB::raw('SUM(amount) as total'))
            ->groupBy('date_key')->pluck('total', 'date_key');

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        // Mapping loop di PHP sekarang sangat ringan karena hanya mencocokkan key yang sudah jadi
        if ($trend === 'weekly') {
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $labels[] = $date->format('D');
                $incomeData[] = $incomeChartRaw->get($date->format('Y-m-d'), 0);
                $expenseData[] = $expenseChartRaw->get($date->format('Y-m-d'), 0);
            }
        } elseif ($trend === 'monthly') {
            $daysInMonth = $now->copy()->startOfMonth()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = $now->copy()->startOfMonth()->addDays($i - 1);
                $labels[] = (string) $i;
                $incomeData[] = $incomeChartRaw->get($date->format('Y-m-d'), 0);
                $expenseData[] = $expenseChartRaw->get($date->format('Y-m-d'), 0);
            }
        } elseif ($trend === 'yearly') {
            for ($i = 1; $i <= 12; $i++) {
                $date = $now->copy()->startOfYear()->addMonths($i - 1);
                $labels[] = $date->format('M');
                // Penyesuaian format key tahunan database
                $key = $date->format('Y-m') ; 
                $incomeData[] = $incomeChartRaw->get($key, 0);
                $expenseData[] = $expenseChartRaw->get($key, 0);
            }
        }

        return response()->json([
            'total_income'  => $totalIncome,
            'total_expense' => $totalExpense,
            'total_balance' => $totalBalance,
            'chart_labels'  => $labels,
            'chart_income'  => $incomeData,
            'chart_expense' => $expenseData,
        ]);
    }

    public function getTopCategories(Request $request): JsonResponse
    {
    $user = auth()->user();

    $trend = $request->query('trend', 'weekly');
    $month = (int) $request->query('month', now()->month);
    $year  = (int) $request->query('year', now()->year);
    $week  = (int) $request->query('week', 1);

    $expenseQuery = $user->transactions()->where('type', 'expense');
    $incomeQuery = $user->transactions()->where('type', 'income');

    if ($trend === 'weekly') {

        $startDay = (($week - 1) * 7) + 1;
        $endDay = min(
            $week * 7,
            \Carbon\Carbon::create($year, $month)->daysInMonth
        );

        $start = \Carbon\Carbon::create($year, $month, $startDay)->startOfDay();
        $end = \Carbon\Carbon::create($year, $month, $endDay)->endOfDay();

        $expenseQuery->whereBetween('transaction_date', [$start, $end]);
        $incomeQuery->whereBetween('transaction_date', [$start, $end]);

    } elseif ($trend === 'monthly') {

        $expenseQuery->whereMonth('transaction_date', $month)
                     ->whereYear('transaction_date', $year);

        $incomeQuery->whereMonth('transaction_date', $month)
                    ->whereYear('transaction_date', $year);

    } elseif ($trend === 'yearly') {

        $expenseQuery->whereYear('transaction_date', $year);
        $incomeQuery->whereYear('transaction_date', $year);
    }

    [$expenses, $totalExpense] = $this->getCategoriesSummary($expenseQuery);
    [$incomes, $totalIncome] = $this->getCategoriesSummary($incomeQuery);

    return response()->json([
        'expenses'      => $expenses,
        'incomes'       => $incomes,
        'total_expense' => $totalExpense,
        'total_income'  => $totalIncome,
    ]);
    }

    // public function getTopCategories(Request $request): JsonResponse
    // {
    //     $user = auth()->user();
    //     $trend = $request->query('trend', 'weekly');
    //     $now = now();

    //     $expenseQuery = $user->transactions()->where('type', 'expense');
    //     $incomeQuery = $user->transactions()->where('type', 'income');

    //     if ($trend === 'weekly') {
    //         $start = $now->copy()->startOfWeek();
    //         $end = $now->copy()->endOfWeek();
    //         $expenseQuery->whereBetween('transaction_date', [$start, $end]);
    //         $incomeQuery->whereBetween('transaction_date', [$start, $end]);
    //     } elseif ($trend === 'monthly') {
    //         $expenseQuery->whereMonth('transaction_date', $now->month)->whereYear('transaction_date', $now->year);
    //         $incomeQuery->whereMonth('transaction_date', $now->month)->whereYear('transaction_date', $now->year);
    //     } elseif ($trend === 'yearly') {
    //         $expenseQuery->whereYear('transaction_date', $now->year);
    //         $incomeQuery->whereYear('transaction_date', $now->year);
    //     }

    //     // Method helper ditarik ke bawah agar controller tetap rapi
    //     [$expenses, $totalExpense] = $this->getCategoriesSummary($expenseQuery);
    //     [$incomes, $totalIncome] = $this->getCategoriesSummary($incomeQuery);

    //     return response()->json([
    //         'expenses'      => $expenses,
    //         'incomes'       => $incomes,
    //         'total_expense' => $totalExpense,
    //         'total_income'  => $totalIncome
    //     ]);
    // }

    // ==========================================
    // UTILITY & OPTIMIZATION HELPERS
    // ==========================================

    private function getDateFormatSql(string $driver, string $trend): string
    {
        // Fungsi SQL dibedakan per Database demi performa maksimal
        return match ($driver) {
            'mysql' => $trend === 'yearly' ? "DATE_FORMAT(transaction_date, '%Y-%m')" : "DATE_FORMAT(transaction_date, '%Y-%m-%d')",
            'pgsql' => $trend === 'yearly' ? "TO_CHAR(transaction_date, 'YYYY-MM')" : "TO_CHAR(transaction_date, 'YYYY-MM-DD')",
            default => $trend === 'yearly' ? "strftime('%m', transaction_date)" : "strftime('%Y-%m-%d', transaction_date)", // SQLite
        };
    }

    private function getCategoriesSummary($query): array
    {
        // Ambil sum total sekali jalan
        $totalAmount = (clone $query)->sum('amount');
        $categories = [];

        if ($totalAmount > 0) {
            // Cukup ambil top 5 baris data yang sudah diagregasi database
            $categories = $query->select('category as category_name', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('category')
                ->orderByDesc('total_amount')
                ->take(5)
                ->get()
                ->map(function ($item) use ($totalAmount) {
                    $item->percentage = round(($item->total_amount / $totalAmount) * 100);
                    return $item;
                });
        }

        return [$categories, $totalAmount];
    }
}