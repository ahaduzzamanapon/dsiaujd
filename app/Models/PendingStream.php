<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingStream extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'url',
        'http_referer',
        'http_origin',
        'category',
        'source',
        'reason',
    ];
}
