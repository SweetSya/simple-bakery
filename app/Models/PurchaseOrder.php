<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
	use HasUuids, SoftDeletes;

	protected $fillable = [
		'order_number',
		'supplier_name',
		'order_date',
		'total_amount',
		'order_summary',
		'is_received',
		'status',
		'notes',
		'pdf_path',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'order_date' => 'datetime',
		'total_amount' => 'decimal:2',
		'order_summary' => 'array',
		'is_received' => 'boolean',
		'status' => 'string',
	];

	/**
	 * Get the details associated with the purchase order.
	 */
	public function details(): HasMany
	{
		return $this->hasMany(DetailPurchaseOrder::class);
	}
}
