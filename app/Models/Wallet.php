<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'user_id',
        'name_wallet',
        'balance',
    ];

    /**
     * Relasi ke User
     * Setiap dompet dimiliki oleh satu user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Transactions
     * Satu dompet bisa punya banyak transaksi.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}