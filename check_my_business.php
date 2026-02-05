<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== INFORMATIONS SUR VOTRE BUSINESS ===\n\n";

// Lister tous les business
echo "Liste de tous les business:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s | %-30s | %-10s | %-20s\n", "ID", "Nom", "Package", "Subscription Status");
echo str_repeat("-", 80) . "\n";

$businesses = DB::table('business')->get();

foreach ($businesses as $business) {
    // Trouver la subscription active
    $subscription = DB::table('subscriptions')
        ->where('business_id', $business->id)
        ->where('status', 'approved')
        ->orderBy('id', 'desc')
        ->first();
    
    if ($subscription) {
        $package = DB::table('packages')->where('id', $subscription->package_id)->first();
        printf("%-5s | %-30s | %-10s | %-20s\n", 
            $business->id, 
            substr($business->name, 0, 30),
            $package ? $package->name : 'N/A',
            $subscription->status
        );
    } else {
        printf("%-5s | %-30s | %-10s | %-20s\n", 
            $business->id, 
            substr($business->name, 0, 30),
            'Aucun',
            'Pas de subscription'
        );
    }
}

echo str_repeat("-", 80) . "\n";

// Afficher les users avec leur business_id de la table users directement
echo "\n\nUtilisateurs récents:\n";
echo str_repeat("-", 100) . "\n";
printf("%-5s | %-25s | %-35s | %-15s\n", "ID", "Username", "Email", "Business ID");
echo str_repeat("-", 100) . "\n";

$users = DB::table('users')
    ->select('id', 'username', 'email', 'business_id')
    ->orderBy('id', 'desc')
    ->limit(20)
    ->get();

foreach ($users as $user) {
    printf("%-5s | %-25s | %-35s | %-15s\n", 
        $user->id,
        substr($user->username ?? 'N/A', 0, 25),
        substr($user->email ?? 'N/A', 0, 35),
        $user->business_id ?? 'N/A'
    );
}

echo str_repeat("-", 100) . "\n";

echo "\n💡 INSTRUCTIONS:\n";
echo "   1. Connectez-vous sur https://yoriinapp.com\n";
echo "   2. Allez dans Paramètres > Profil pour voir votre email/username\n";
echo "   3. Cherchez votre email dans la liste ci-dessus\n";
echo "   4. Notez votre Business ID\n";
echo "   5. Vérifiez dans la première liste quel package vous avez\n\n";
echo "   ✅ Tous ces packages ont maintenant le module Multiservices activé!\n";