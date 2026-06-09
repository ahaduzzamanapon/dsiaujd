<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_permanent' => 'boolean',
        'start_time' => 'datetime',
        'expire_time' => 'datetime',
        'show_in_events' => 'boolean',
        'show_in_sports' => 'boolean',
        'show_in_tv' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function servers()
    {
        return $this->hasMany(StreamServer::class)->orderBy('order');
    }
}
