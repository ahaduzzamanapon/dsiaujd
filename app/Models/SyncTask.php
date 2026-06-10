<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncTask extends Model
{
    protected $fillable = [
        'type',
        'name',
        'url',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
