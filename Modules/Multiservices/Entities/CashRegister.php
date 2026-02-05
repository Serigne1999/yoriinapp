<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CashRegister extends Model
{
    protected $table = 'multiservices_cash_registers';
    
    protected $fillable = [
        'business_id',
        'location_id',
        'balance',
        'opening_balance',
        'last_opening_date',
    ];
    
    protected $casts = [
        'balance' => 'decimal:4',
        'opening_balance' => 'decimal:4',
        'last_opening_date' => 'date',
    ];
    
    /**
     * Relation avec Business
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }
    
    /**
     * Relation avec Location
     */
    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }
    
    /**
     * Obtenir ou créer la caisse pour une location
     */
    public static function getOrCreateForLocation($businessId, $locationId)
    {
        return self::firstOrCreate(
            [
                'business_id' => $businessId,
                'location_id' => $locationId,
            ],
            [
                'balance' => 0,
                'opening_balance' => 0,
                'last_opening_date' => Carbon::today(),
            ]
        );
    }
    
    /**
     * Ouvrir la caisse pour la journée
     */
    public function openDay($openingBalance = null)
    {
        $today = Carbon::today();
        
        // Si c'est un nouveau jour, mettre à jour le solde d'ouverture
        if (!$this->last_opening_date || !$this->last_opening_date->isToday()) {
            $this->opening_balance = $openingBalance ?? $this->balance;
            $this->last_opening_date = $today;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Ajouter un montant à la caisse (Dépôt)
     */
    public function addCash($amount)
    {
        $this->balance += $amount;
        $this->save();
        
        return $this;
    }
    
    /**
     * Retirer un montant de la caisse (Retrait)
     */
    public function removeCash($amount)
    {
        $this->balance -= $amount;
        $this->save();
        
        return $this;
    }
    
    /**
     * Scope pour filtrer par business
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
