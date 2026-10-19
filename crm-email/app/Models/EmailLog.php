<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pic',
        'customer_name',
        'customer_email',
        'customer_phone',
        'case_id',
        'subject',
        'body',
    ];
}
