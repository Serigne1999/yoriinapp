<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotification extends Model
{
    protected $fillable = ['transaction_id', 'client_phone', 'message', 'status'];
}