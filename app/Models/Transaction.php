<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi (sesuai gambar skema kamu)
    protected $fillable = [
        'user_id',
        'wallet_id',
        'to_wallet_id',
        'title',
        'amount',
        'type',
        'category',
        'attachment',
        'note',
        'transaction_date',
    ];

    // Mengatur tipe data agar otomatis menjadi objek Carbon (waktu)
    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi: Transaksi ini milik siapa?
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Transaksi ini menggunakan dompet mana?
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relasi: Jika transfer, tujuannya ke dompet mana?
     */
    public function destinationWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
}