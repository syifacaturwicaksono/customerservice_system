<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaReminderSetting extends Model
{
    protected $fillable = [
        'is_active',
        'threshold_days',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
