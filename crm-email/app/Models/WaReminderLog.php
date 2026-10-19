<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaReminderLog extends Model
{
    protected $fillable = [
        'ce_name',
        'phone',
        'case_id',
        'case_status',
        'tat_open_case',
        'message',
        'status',
        'sent_date',
    ];

    protected $casts = [
        'sent_date' => 'date',
    ];
}
