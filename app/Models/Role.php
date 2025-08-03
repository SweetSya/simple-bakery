<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

class Role extends Model implements ContractsAuditable
{
    use SoftDeletes, HasUuids, Auditable;

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
