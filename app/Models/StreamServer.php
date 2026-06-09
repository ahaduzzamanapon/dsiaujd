<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreamServer extends Model
{
    protected $guarded = [];

    public function stream()
    {
        return $this->belongsTo(Stream::class);
    }
}
