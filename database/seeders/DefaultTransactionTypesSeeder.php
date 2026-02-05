<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Multiservices\Entities\TransactionType;

class DefaultTransactionTypesSeeder extends Seeder
{
    public function run()
    {
        $businesses = \App\Business::all();
        
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
        
        foreach ($businesses as $business) {
            // Vérifier si le business a déjà des types
            $hasTypes = TransactionType::where('business_id', $business->id)->exists();
            
            if (!$hasTypes) {
                echo "✅ Création des types pour business #{$business->id} - {$business->name}\n";
                
                foreach ($defaultTypes as $type) {
                    TransactionType::create([
                        'business_id' => $business->id,
                        'name' => $type['name'],
                        'code' => $type['code'],
                        'description' => $type['description'],
                        'is_active' => $type['is_active'],
                    ]);
                }
            } else {
                echo "ℹ️  Business #{$business->id} a déjà des types\n";
            }
        }
        
        echo "\n🎉 Terminé !\n";
    }
}