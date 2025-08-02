<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPurchaseOrder extends Model
{

    use HasUuids, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
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
     * Get the purchase order that owns the detail.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product associated with the detail.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
