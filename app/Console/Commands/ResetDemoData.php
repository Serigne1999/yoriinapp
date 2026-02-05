<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset-data';
    protected $description = 'Nettoie les données utilisateurs et produits récents du compte démo sans supprimer le business.';

    public function handle()
    {
        $businessId = 11;
        $locationId = 1;

        // ⏱ Supprimer les données créées il y a moins d'1 jour (modifie ici si tu veux 1h)
        $cutoff = Carbon::now()->subDay();

        $this->info("🔄 Suppression des données créées après $cutoff pour le business #$businessId...");

        // Vérification de sécurité
        $business = DB::table('business')->find($businessId);
        if (!$business) {
            $this->error("❌ Business ID $businessId non trouvé. Abandon de la commande.");
            return;
        }

        // ✅ Supprimer transactions récentes
        DB::table('transactions')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', $cutoff)
            ->delete();

        // ✅ Supprimer sell lines liées aux transactions
        DB::table('transaction_sell_lines')
            ->whereIn('transaction_id', function ($query) use ($businessId, $cutoff) {
                $query->select('id')->from('transactions')
                    ->where('business_id', $businessId)
                    ->where('created_at', '>=', $cutoff);
            })
            ->delete();

        // ✅ Supprimer clients récents
        DB::table('contacts')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', $cutoff)
            ->delete();

        // ✅ Supprimer utilisateurs créés récemment (sauf admin id 1)
        DB::table('users')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', $cutoff)
            ->where('id', '!=', 1)
            ->delete();

        // ✅ Supprimer ouvertures de caisse récentes
        DB::table('cash_registers')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', $cutoff)
            ->delete();

        // ✅ Supprimer documents récents
        DB::table('document_layouts')
            ->where('business_id', $businessId)
            ->where('created_at', '>=', $cutoff)
            ->delete();

        // 🔒 Restaurer la langue française pour la démo si altérée
        DB::table('business')
            ->where('id', $businessId)
            ->update(['language' => 'fr']);

        $this->info("✅ Données créées récemment nettoyées. Le business a été conservé et la langue remise à fr.");
    }
}
