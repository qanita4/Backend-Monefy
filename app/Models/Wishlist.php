<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount', 
        'notes',       
        'status',
    ];

    /**
     * Relasi: Satu wishlist otomatis mencatat satu transaksi jika dibeli
     * (Optional, tapi bagus buat dipasang karena di controller pakai ->load('transactions'))
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wishlist_id');
    }
}