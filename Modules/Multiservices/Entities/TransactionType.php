<?php
namespace Modules\Multiservices\Entities;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $table = 'multiservices_transaction_types';
    
    protected $fillable = [
        'business_id',
        'name',
        'code',
        'icon',
        'color',
        'is_active',
        'description',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
    
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
    
    public function getBadgeHtml()
    {
        return '<span class="label" style="background-color: ' . $this->color . '; color: #fff; padding: 5px 10px; border-radius: 3px;">' 
               . '<i class="fa ' . $this->icon . '"></i> ' 
               . $this->name 
               . '</span>';
    }
    
    public function getStatusBadge()
    {
        return $this->is_active 
            ? '<span class="label label-success">Actif</span>' 
            : '<span class="label label-default">Inactif</span>';
    }
}