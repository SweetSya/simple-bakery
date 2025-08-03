<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobWatcher extends Model
{
    use HasUuids;
    protected $fillable = [
        'job_id',
        'user_id',
        'job_type',
        'job_data',
        'status',
    ];
    protected $casts = [
        'job_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
