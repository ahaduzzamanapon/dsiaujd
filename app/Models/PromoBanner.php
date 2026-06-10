<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoBanner extends Model
{
    protected $guarded = [];

    public function stream1()
    {
        return $this->belongsTo(Stream::class, 'stream1_id');
    }

    public function stream2()
    {
        return $this->belongsTo(Stream::class, 'stream2_id');
    }

    public function stream3()
    {
        return $this->belongsTo(Stream::class, 'stream3_id');
    }
}
