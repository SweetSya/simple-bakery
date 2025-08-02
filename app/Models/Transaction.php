<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = [
        'transaction_reference',
        'user_id',
        'server_id',
        'transaction_date',
        'total_amount',
        'payment_details',
        'status',
        'is_completed',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'payment_details' => 'array',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the details associated with the transaction.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaction::class);
    }
    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get the server that owns the transaction.
     */
    public function servant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'server_id');
    }
}
