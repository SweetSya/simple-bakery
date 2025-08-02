<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailTransaction extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the transaction that owns the detail.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the product associated with the detail.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
