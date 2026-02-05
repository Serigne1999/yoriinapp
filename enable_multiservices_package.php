<?php
require __DIR__.'/bootstrap/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Business;
use App\Models\Subscription;

$business_id = 24; // Business concerné

echo "🔍 Activation du module Multiservices pour business_id {$business_id}...\n\n";

$business = Business::find($business_id);

if (!$business) {
    echo "❌ Business {$business_id} non trouvé\n";
    exit;
}

// Trouver l'abonnement actif
$subscription = Subscription::where('business_id', $business_id)
    ->whereDate('start_date', '<=', now())
    ->whereDate('end_date', '>=', now())
    ->first();

if (!$subscription) {
    echo "❌ Aucun abonnement actif trouvé\n";
    exit;
}

echo "📦 Package actuel : {$subscription->package->name}\n";
echo "🔑 Permissions actuelles : {$subscription->custom_permissions}\n\n";

// Ajouter multiservices aux custom_permissions
$current_permissions = json_decode($subscription->custom_permissions, true) ?? [];

if (!in_array('multiservices', $current_permissions)) {
    $current_permissions[] = 'multiservices';
    $subscription->custom_permissions = json_encode($current_permissions);
    $subscription->save();
    
    echo "✅ Module Multiservices activé !\n";
} else {
    echo "ℹ️ Module Multiservices déjà activé\n";
}

echo "\n🔑 Nouvelles permissions : {$subscription->custom_permissions}\n";
echo "\n✅ Terminé ! Reconnectez-vous pour voir le menu.\n";
