<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'name',
        'description'
    ];

    // Relationship with other models
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
