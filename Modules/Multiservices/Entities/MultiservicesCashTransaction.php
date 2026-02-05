<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;

class MultiservicesCashTransaction extends Model
{
    protected $fillable = [
        'cash_register_id', 'type', 'amount',
        'balance_before', 'balance_after',
        'multiservice_transaction_id', 'notes', 'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // Relations
    public function cashRegister()
    {
        return $this->belongsTo(MultiservicesCashRegister::class, 'cash_register_id');
    }

    public function multiserviceTransaction()
    {
        return $this->belongsTo(MultiserviceTransaction::class, 'multiservice_transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
