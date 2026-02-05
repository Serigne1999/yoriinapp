<?php

namespace App\Services;

use App\Models\WhatsappNotification;

class WhatsappSenderService
{
    public static function queueMessage($transaction_id, $client_phone, $message)
    {
        return WhatsappNotification::create([
            'transaction_id' => $transaction_id,
            'client_phone' => $client_phone,
            'message' => $message,
            'status' => 'pending'
        ]);
    }
}
