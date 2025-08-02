<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image_path',
        'is_active',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
        'stock' => 'integer',
    ];
    /**
     * Get the transactions associated with the product.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    /**
     * Get the purchase orders associated with the product.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(DetailPurchaseOrder::class);
    }
}
