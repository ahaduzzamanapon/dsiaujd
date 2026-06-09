<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function streams()
    {
        return $this->belongsToMany(Stream::class)->withTimestamps();
    }
}
