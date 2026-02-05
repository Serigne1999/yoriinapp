<?php

namespace Modules\Multiservices\Utils;

use App\CashRegister;
use Carbon\Carbon;

class CashRegisterHelper
{
    /**
     * Obtenir la caisse ouverte pour une location
     * Si aucune caisse n'est ouverte, en créer une automatiquement
     */
    public static function getOpenCashRegister($businessId, $locationId)
    {
        // Chercher une caisse ouverte pour cette location
        $cashRegister = CashRegister::where('business_id', $businessId)
            ->where('location_id', $locationId)
            ->where('status', 'open')
            ->whereDate('created_at', Carbon::today())
            ->first();
        
        // Si aucune caisse ouverte, en créer une automatiquement
        if (!$cashRegister) {
            $cashRegister = CashRegister::create([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'user_id' => auth()->id(),
                'status' => 'open',
                'closing_amount' => 0,
                'total_card_slips' => 0,
                'total_cheques' => 0,
            ]);
        }
        
        return $cashRegister;
    }
    
    /**
     * Créer un mouvement de caisse
     */
    public static function createCashTransaction($cashRegisterId, $amount, $type, $transactionType = 'multiservices', $transactionId = null)
    {
        return \App\CashRegisterTransaction::create([
            'cash_register_id' => $cashRegisterId,
            'amount' => abs($amount),
            'pay_method' => 'cash',
            'type' => $type, // 'credit' ou 'debit'
            'transaction_type' => $transactionType,
            'transaction_id' => $transactionId,
        ]);
    }
}
