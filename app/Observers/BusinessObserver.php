<?php

namespace App\Observers;

use App\Business;
use Modules\Multiservices\Entities\TransactionType;

class BusinessObserver
{
    public function created(Business $business)
    {
        // Créer les types de transactions par défaut
        $defaultTypes = [
            [
                'name' => 'Dépôt',
                'code' => 'deposit',
                'description' => 'Dépôt d\'argent sur un compte opérateur',
                'is_active' => 1,
            ],
            [
                'name' => 'Retrait',
                'code' => 'withdrawal',
                'description' => 'Retrait d\'argent depuis un compte opérateur',
                'is_active' => 1,
            ],
            [
                'name' => 'Transfert',
                'code' => 'transfer',
                'description' => 'Transfert d\'argent entre comptes',
                'is_active' => 1,
            ],
        ];
        
        foreach ($defaultTypes as $type) {
            TransactionType::create([
                'business_id' => $business->id,
                'name' => $type['name'],
                'code' => $type['code'],
                'description' => $type['description'],
                'is_active' => $type['is_active'],
            ]);
        }
    }
}