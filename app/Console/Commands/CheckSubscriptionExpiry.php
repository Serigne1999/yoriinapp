<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiryMail;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';
    protected $description = 'Envoie un email aux abonnés dont la souscription expire bientôt';

    public function handle()
    {
        $today = Carbon::now();
        $expiring = Subscription::whereDate('end_date', '<=', $today->addDays(3))
            ->where('status', 'active')
            ->get();

        foreach ($expiring as $sub) {
            Mail::to($sub->user->email)->send(new SubscriptionExpiryMail($sub));
            $this->info("Email envoyé à {$sub->user->email}");
        }
    }
}
