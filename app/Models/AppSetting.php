<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_mandatory_update' => 'boolean',
        'promo_show_alert' => 'boolean',
        'promo_banner_enabled' => 'boolean',
    ];
}
