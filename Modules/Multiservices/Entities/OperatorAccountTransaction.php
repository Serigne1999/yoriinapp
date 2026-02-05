<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;

class OperatorAccountTransaction extends Model
{
    protected $fillable = [
        'operator_account_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(OperatorAccount::class, 'operator_account_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    // Helpers
    public function getTypeLabel()
    {
        $labels = [
            'deposit' => 'Dépôt',
            'withdrawal' => 'Retrait',
            'adjustment' => 'Ajustement',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}
