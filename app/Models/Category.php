<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'order' => 'integer',
    ];

    public function streams()
    {
        return $this->belongsToMany(Stream::class)->withTimestamps();
    }
}
