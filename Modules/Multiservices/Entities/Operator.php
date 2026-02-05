<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use SoftDeletes;

    protected $table = 'multiservice_operators';

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'color',
        'logo',
        'icon',
        'is_active',
        'description',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo('App\Business');
    }

    public function accounts()
    {
        return $this->hasMany(OperatorAccount::class, 'operator', 'code');
    }

    public function transactions()
    {
        return $this->hasMany(MultiserviceTransaction::class, 'operator', 'code');
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    // Helpers
    public function getFormattedNameAttribute()
    {
        return $this->name;
    }

    public function getBadgeHtmlAttribute()
    {
        return '<span class="label" style="background-color: ' . $this->color . '; padding: 5px 10px;">' 
               . strtoupper($this->name) 
               . '</span>';
    }

    // Static helper pour obtenir les opérateurs d'un business
    public static function getOperatorsForBusiness($businessId, $activeOnly = true)
    {
        $query = self::forBusiness($businessId)->ordered();
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->get();
    }

    // Convertir en format compatible avec le code existant
    public function toCompatibleArray()
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }
}
