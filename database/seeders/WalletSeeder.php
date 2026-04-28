<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wallet;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        // Data Wallet untuk User ID 1 (Rafif)
        Wallet::create([
            'user_id' => 1,
            'name_wallet' => 'Bank BCA',
            'balance' => 2500000.00,
        ]);

        Wallet::create([
            'user_id' => 1,
            'name_wallet' => 'Gopay',
            'balance' => 500000.00,
        ]);

        Wallet::create([
            'user_id' => 1,
            'name_wallet' => 'Uang Tunai (Cash)',
            'balance' => 150000.00,
        ]);

        // Data Wallet untuk User ID 2 (Qanita)
        Wallet::create([
            'user_id' => 2,
            'name_wallet' => 'Bank Mandiri',
            'balance' => 4200000.00,
        ]);

        Wallet::create([
            'user_id' => 2,
            'name_wallet' => 'ShopeePay',
            'balance' => 250000.00,
        ]);
    }
}