<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

public function up(): void
{
    Schema::table('wallets', function (Blueprint $table) {
        // Menambahkan kategori dompet
        $table->enum('category', ['Bank Account', 'Cash', 'E-Wallet'])->default('Cash');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
