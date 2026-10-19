<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    protected $table = 'wa_logs';

    protected $fillable = [
        'pic',
        'customer_name',
        'customer_phone',
        'case_id',
        'message',
        'status'
    ];
}