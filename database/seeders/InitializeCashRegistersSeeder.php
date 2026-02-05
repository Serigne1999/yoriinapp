<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Multiservices\Entities\CashRegister;
use App\BusinessLocation;
use Carbon\Carbon;

class InitializeCashRegistersSeeder extends Seeder
{
    public function run()
    {
        $locations = BusinessLocation::all();
        
        foreach ($locations as $location) {
            // Vérifier si la caisse existe déjà
            $exists = CashRegister::where('business_id', $location->business_id)
                                 ->where('location_id', $location->id)
                                 ->exists();
            
            if (!$exists) {
                CashRegister::create([
                    'business_id' => $location->business_id,
                    'location_id' => $location->id,
                    'balance' => 0,
                    'opening_balance' => 0,
                    'last_opening_date' => Carbon::today(),
                ]);
                
                echo "✅ Caisse créée pour {$location->name} (Business #{$location->business_id})\n";
            } else {
                echo "ℹ️  Caisse existe déjà pour {$location->name}\n";
            }
        }
        
        echo "\n🎉 Terminé !\n";
    }
}
