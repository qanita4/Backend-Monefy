<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    protected $fillable = [
    'user_id',
    'provider',
    'account_number',
    'amount',
    'due_date',
    'cycle',
    'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi ke User.
     * Setiap tagihan pasti milik satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
