<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Business;
use App\User;

class MultiserviceTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'location_id',
        'operator',
        'transaction_type',
        'type_id',
        'sender_name',
        'sender_phone',
        'sender_id_number',
        'receiver_name',
        'receiver_phone',
        'receiver_id_number',
        'amount',
        'fee',
        'total',
        'profit',
        'reference_number',
        'status',
        'notes',
        'payment_method',
        'completed_at',
        'completed_by',
        'canceled_at',
        'canceled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',
        'profit' => 'decimal:2',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo('App\Business');
    }
    public function location()  // ← AJOUTER
    {
        return $this->belongsTo('App\BusinessLocation', 'location_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function completedBy()
    {
        return $this->belongsTo('App\User', 'completed_by');
    }
    
    public function canceledBy()
    {
        return $this->belongsTo('App\User', 'canceled_by');
    }
    public function transactionType()
    {
        return $this->belongsTo(\Modules\Multiservices\Entities\TransactionType::class, 'type_id');
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByOperator($query, $operator)
    {
        return $query->where('operator', $operator);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helpers
    public function canBeModified()
    {
        return $this->status === 'pending';
    }

    public function markAsCompleted($userId)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);
    }

    public function markAsCanceled($userId, $reason = null)
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'canceled_by' => $userId,
            'cancel_reason' => $reason,
        ]);
    }

    // Auto-generate reference number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'MS' . date('Ymd') . strtoupper(uniqid());
            }
        });
    }
}
