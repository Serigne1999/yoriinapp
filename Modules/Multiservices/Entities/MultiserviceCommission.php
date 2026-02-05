<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\Business;

class MultiserviceCommission extends Model
{
    protected $fillable = [
        'business_id',
        'operator',
        'transaction_type',
        'commission_type',
        'commission_value',
        'min_amount',
        'max_amount',
        'min_commission',
        'max_commission',
        'is_active',
        'priority',
        'description',
    ];

    protected $casts = [
        'commission_value' => 'decimal:4',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'min_commission' => 'decimal:2',
        'max_commission' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOperator($query, $operator)
    {
        return $query->where('operator', $operator);
    }

    public function scopeForAmount($query, $amount)
    {
        return $query->where(function ($q) use ($amount) {
            $q->where(function ($subQ) use ($amount) {
                $subQ->whereNull('min_amount')
                     ->orWhere('min_amount', '<=', $amount);
            })->where(function ($subQ) use ($amount) {
                $subQ->whereNull('max_amount')
                     ->orWhere('max_amount', '>=', $amount);
            });
        });
    }

    // Calcul de la commission
    public function calculateCommission($amount)
    {
        if ($this->commission_type === 'fixed') {
            $commission = $this->commission_value;
        } else {
            $commission = ($amount * $this->commission_value) / 100;
        }

        // Appliquer les limites min/max
        if ($this->min_commission && $commission < $this->min_commission) {
            $commission = $this->min_commission;
        }

        if ($this->max_commission && $commission > $this->max_commission) {
            $commission = $this->max_commission;
        }

        return round($commission, 2);
    }

    // Trouver la commission applicable
    public static function findApplicable($businessId, $operator, $transactionType, $amount)
    {
        return static::forBusiness($businessId)
            ->active()
            ->forOperator($operator)
            ->where(function ($query) use ($transactionType) {
                $query->where('transaction_type', $transactionType)
                      ->orWhere('transaction_type', 'all');
            })
            ->forAmount($amount)
            ->orderBy('priority', 'desc')
            ->first();
    }
}
