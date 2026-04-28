<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Simulasi Rafif beli Kopi seharga 25.000 pakai Bank BCA (Wallet ID 1)
        \App\Models\Transaction::create([
            'user_id' => 1, 
            'wallet_id' => 1, 
            'title' => 'Beli Kopi Kenangan',
            'amount' => 25000,
            'type' => 'expense',
            'category' => 'Food & Beverage',
            'transaction_date' => now(),
        ]);

            // Update manual saldo wallet di seeder untuk tes pertama kali
            $wallet = \App\Models\Wallet::find(1);
            $wallet->decrement('balance', 25000);
}
}
