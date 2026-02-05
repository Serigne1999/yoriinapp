@component('mail::message')
# Notification d’expiration d’abonnement

Salam {{ $subscription->user->first_name ?? 'Cher client' }},

Nous espérons que vous profitez pleinement de votre abonnement **{{ $subscription->package->name }}** sur **{{ config('app.name') }}**.

Votre abonnement arrivera à expiration le **{{ $subscription->end_date->format('d/m/Y') }}**.

Veuillez renouveler avant cette date pour continuer à bénéficier de nos services sans interruption.

@component('mail::button', ['url' => url('/subscription/'.$subscription->id.'/renew')])
Renouveler maintenant
@endcomponent

Merci pour votre confiance,  
**{{ config('app.name') }}**  
📞 Service client : +221 784655069  
@endcomponent
