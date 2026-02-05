@extends('layouts.guest')
@section('title', 'Commande confirmée - ' . $business->name)
@section('content')
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f5f5f5;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.confirmation-container {
    max-width: 480px;
    margin: 0 auto;
    background: white;
    min-height: 100vh;
    position: relative;
}

.header-bar {
    background: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
}

.header-bar h3 {
    font-size: 16px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
}

.status-section {
    text-align: center;
    padding: 40px 20px;
}

.status-icon {
    width: 80px;
    height: 80px;
    background: #e8f5e9;
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-icon i {
    font-size: 40px;
    color: #4caf50;
}

.status-icon.pending {
    background: #fff3e0;
}

.status-icon.pending i {
    color: #ff9800;
}

.status-title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.status-subtitle {
    font-size: 15px;
    color: #666;
    line-height: 1.5;
}

.order-reference {
    background: #f8f9fa;
    padding: 15px 20px;
    margin: 20px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reference-label {
    font-size: 14px;
    color: #666;
}

.reference-number {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.order-details {
    padding: 0 20px 20px;
}

.detail-section {
    margin-bottom: 25px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    padding: 15px;
}

.order-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    font-size: 15px;
    font-weight: 500;
    color: #333;
    margin-bottom: 4px;
}

.item-meta {
    font-size: 13px;
    color: #666;
}

.item-price {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    text-align: right;
}

.total-section {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 12px;
    margin: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 15px;
}

.total-row.final {
    border-top: 1px solid #e0e0e0;
    padding-top: 12px;
    margin-top: 8px;
    font-size: 18px;
    font-weight: 600;
}

.payment-section {
    padding: 20px;
    background: white;
}

.payment-step {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.step-number {
    width: 32px;
    height: 32px;
    background: #333;
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 10px;
}

.step-title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.step-description {
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}

.bank-icons {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    justify-content: center;
}

.bank-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    border: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-buttons {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    max-width: 480px;
    margin: 0 auto;
}

.btn-primary {
    width: 100%;
    background: #0066FF;
    color: white;
    border: none;
    padding: 16px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #0052CC;
    transform: translateY(-2px);
}

.btn-primary.wave {
    background: #0066FF;
}

.btn-primary.whatsapp {
    background: #25D366;
}

.btn-primary.whatsapp:hover {
    background: #128C7E;
}

.btn-secondary {
    width: 100%;
    background: #333;
    color: white;
    border: none;
    padding: 16px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #555;
}

.or-divider {
    text-align: center;
    margin: 15px 0;
    color: #999;
    font-size: 14px;
    font-weight: 600;
}

.info-banner {
    background: #e3f2fd;
    padding: 12px 15px;
    margin: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-banner i {
    color: #1976d2;
    font-size: 20px;
}

.info-banner p {
    font-size: 13px;
    color: #1565c0;
    margin: 0;
}

@media (max-width: 480px) {
    .confirmation-container {
        max-width: 100%;
    }
    
    .action-buttons {
        max-width: 100%;
    }
}
</style>

<div class="confirmation-container">
    {{-- Header --}}
    <div class="header-bar">
        <h3>Commande #{{str_pad($order->id, 6, '0', STR_PAD_LEFT)}}</h3>
        <button class="close-btn" onclick="window.location.href='{{url('/catalogue/' . $business->id . '/' . $business_location->id)}}'">×</button>
    </div>

    {{-- Status Section --}}
    <div class="status-section">
        @if(session()->has('wave_payment_link') && request()->get('payment') == 'pending')
            {{-- État 1: En attente de paiement Wave --}}
            <div class="status-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <h2 class="status-title">En attente de paiement</h2>
            <p class="status-subtitle">Cliquez sur le bouton Wave ci-dessous pour payer</p>
        
        @elseif(request()->get('payment') == 'completed')
            {{-- État 2: Paiement Wave complété --}}
            <div class="status-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="status-title">Paiement effectué !</h2>
            <p class="status-subtitle">Merci ! Confirmez maintenant via WhatsApp</p>
        
        @else
            {{-- État 3: Autre mode de paiement (livraison, etc.) --}}
            <div class="status-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="status-title">Commande confirmée</h2>
            <p class="status-subtitle">Envoyez un message pour confirmer votre commande</p>
        @endif
    </div>

    {{-- Reference Number --}}
    <div class="order-reference">
        <span class="reference-label">Référence</span>
        <span class="reference-number">#{{str_pad($order->id, 6, '0', STR_PAD_LEFT)}}</span>
    </div>

    {{-- Order Items --}}
<div class="detail-section">
    <h3 class="section-title">Articles commandés</h3>
    @php
        $items = is_string($order->items) ? json_decode($order->items, true) : (is_array($order->items) ? $order->items : (array)$order->items);
    @endphp
    
    @if(is_array($items) && count($items) > 0)
        @foreach($items as $item)
            @php
                $itemName = is_object($item) ? ($item->name ?? 'Produit') : ($item['name'] ?? 'Produit');
                $itemQty = is_object($item) ? ($item->quantity ?? 1) : ($item['quantity'] ?? 1);
                $itemPrice = is_object($item) ? ($item->price ?? 0) : ($item['price'] ?? 0);
                $itemImage = is_object($item) ? ($item->image ?? asset('img/default.png')) : ($item['image'] ?? asset('img/default.png'));
                
                // Nettoyer le nom
                $itemName = str_replace([' - DUMMY', 'DUMMY'], '', $itemName);
                $itemName = trim($itemName);
            @endphp
            
            <div class="order-item">
                <img src="{{$itemImage}}" alt="{{$itemName}}" class="item-image">
                <div class="item-details">
                    <div class="item-name">{{$itemName}}</div>
                    <div class="item-meta">Qté: {{$itemQty}}</div>
                </div>
                <div class="item-price">
                    <span class="display_currency" data-currency_symbol="true">{{$itemPrice * $itemQty}}</span>
                </div>
            </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 20px; color: #999;">
                <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 10px;"></i>
                <p>Aucun article trouvé</p>
            </div>
        @endif
    </div>

    {{-- Total --}}
    <div class="total-section">
        <div class="total-row">
            <span>Sous-total</span>
            <span class="display_currency" data-currency_symbol="true">{{$order->total_amount}}</span>
        </div>
        <div class="total-row">
            <span>Livraison</span>
            <span>À définir</span>
        </div>
        <div class="total-row final">
            <span>Total</span>
            <span class="display_currency" data-currency_symbol="true">{{$order->total_amount}}</span>
        </div>
    </div>

    {{-- Payment Instructions --}}
    @if(session()->has('wave_payment_link') && !request()->has('from_wave'))
        <div class="payment-section">
            <div class="payment-step">
                <div class="step-number">1</div>
                <div class="step-title">Cliquez sur "Payer avec Wave"</div>
                <div class="step-description">Vous serez redirigé vers l'application Wave pour effectuer le paiement de manière sécurisée.</div>
            </div>

            <div class="payment-step">
                <div class="step-number">2</div>
                <div class="step-title">Confirmez le paiement</div>
                <div class="step-description">Suivez les instructions dans l'application Wave pour valider la transaction.</div>
            </div>

            <div class="payment-step">
                <div class="step-number">3</div>
                <div class="step-title">Revenez et confirmez</div>
                <div class="step-description">Après le paiement, revenez ici et cliquez sur "Envoyer WhatsApp" pour confirmer.</div>
            </div>
        </div>

        <div style="height: 140px;"></div>

        {{-- Action Buttons - Wave --}}
        <div class="action-buttons">
            <a href="{{ session('wave_payment_link') }}" class="btn-primary wave">
                <i class="fas fa-bolt"></i>
                Payer avec Wave (<span class="display_currency" data-currency_symbol="true">{{$order->total_amount}}</span>)
            </a>
            <div class="info-banner">
                <i class="fas fa-shield-alt"></i>
                <p>Paiement 100% sécurisé via Wave</p>
            </div>
        </div>

        @php
            session()->forget('wave_payment_link');
        @endphp
    @else
        {{-- WhatsApp Message Preparation --}}
        @php
            $settings = json_decode($business->productcatalogue_settings, true);
            $whatsapp_number = preg_replace('/[^0-9]/', '', $settings['whatsapp_contact'] ?? '');
            
            if (empty($whatsapp_number)) {
                $owner = DB::table('users')->find($business->owner_id);
                $whatsapp_number = preg_replace('/[^0-9]/', '', $owner->contact_number ?? '');
            }
            
            if (!empty($whatsapp_number) && !str_starts_with($whatsapp_number, '221')) {
                $whatsapp_number = '221' . $whatsapp_number;
            }
            
            // Construction du message SANS encoder d'abord
            $message = "*CONFIRMATION DE COMMANDE*\n\n";
            $message .= "*N° de commande:* #" . str_pad($order->id, 6, '0', STR_PAD_LEFT) . "\n\n";
            $message .= "*Client:*\n";
            $message .= "Nom: " . $order->customer_name . "\n";
            $message .= "Tél: " . $order->customer_phone . "\n";
            
            if (!empty($order->customer_address) && $order->customer_address != 'Table') {
                $message .= "Adresse: " . $order->customer_address . "\n";
            }
            
            if (!empty($order->table_number)) {
                $message .= "Table: " . $order->table_number . "\n";
            }
            
            $message .= "\n*Commande:*\n";
            
            if (!empty($items) && is_array($items)) {
                foreach($items as $item) {
                    $itemName = isset($item['name']) ? $item['name'] : 'Produit';
                    $itemQty = isset($item['quantity']) ? $item['quantity'] : 1;
                    $itemPrice = isset($item['price']) ? $item['price'] : 0;
                    
                    $itemName = str_replace([' - DUMMY', 'DUMMY'], '', $itemName);
                    $itemName = trim($itemName);
                    
                    $message .= $itemName . " x " . number_format($itemPrice * $itemQty, 0, ',', ' ') . " FCFA\n";
                }
            }
            
            $message .= "\n*Total: " . number_format($order->total_amount, 0, ',', ' ') . " FCFA*\n\n";
            
            if (request()->get('payment') == 'completed') {
                $message .= "*Paiement effectué via Wave*\n";
            }
            
            $message .= "*Commande confirmée*\n\n";
            $message .= "Merci !";
            
            $whatsapp_url = !empty($whatsapp_number) ? "https://wa.me/" . $whatsapp_number . "?text=" . rawurlencode($message) : '#';
            $sms_url = !empty($whatsapp_number) ? "sms:" . $whatsapp_number . "?&body=" . rawurlencode($message) : '#';


        @endphp

        <div style="height: 180px;"></div>

        {{-- Action Buttons - Contact --}}
        <div class="action-buttons">
            @if(request()->has('from_wave'))
                <div class="info-banner" style="background: #e8f5e9; margin-bottom: 15px;">
                    <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                    <p style="color: #2e7d32;">Paiement Wave effectué avec succès !</p>
                </div>
            @endif

            @if(!empty($whatsapp_number))
                <a href="{{ $whatsapp_url }}" class="btn-primary whatsapp" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    Envoyez ma confirmation par WhatsApp
                </a>
                
                <div class="or-divider">ou</div>
                
                <a href="{{ $sms_url }}" class="btn-secondary">
                    <i class="fas fa-sms"></i>
                    Ou par Sms
                </a>
            @else
                <div style="text-align: center; color: #d32f2f; padding: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Numéro de contact non configuré</p>
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Currency fields -->
<input type="hidden" id="__code" value="{{$business->currency->code}}">
<input type="hidden" id="__symbol" value="{{$business->currency->symbol}}">
<input type="hidden" id="__thousand" value="{{$business->currency->thousand_separator}}">
<input type="hidden" id="__decimal" value="{{$business->currency->decimal_separator}}">
<input type="hidden" id="__symbol_placement" value="{{$business->currency->currency_symbol_placement}}">
<input type="hidden" id="__precision" value="{{$business->currency_precision}}">
<input type="hidden" id="__quantity_precision" value="{{$business->quantity_precision}}">

@stop

@section('javascript')
<script type="text/javascript">
(function($) {
    $(document).ready(function() {
        // Configuration devises
        __currency_symbol = $('input#__symbol').val();
        __currency_thousand_separator = $('input#__thousand').val();
        __currency_decimal_separator = $('input#__decimal').val();
        __currency_symbol_placement = $('input#__symbol_placement').val();
        __currency_precision = $('input#__precision').length > 0 ? $('input#__precision').val() : 2;
        __quantity_precision = $('input#__quantity_precision').length > 0 ? $('input#__quantity_precision').val() : 2;

        __currency_convert_recursively($('.confirmation-container'));
        
        // ✅ Variables de détection
        var currentOrderId = '{{$order->id}}';
        var urlParams = new URLSearchParams(window.location.search);
        var paymentStatus = urlParams.get('payment');
        
        console.log('=== DEBUG WAVE ===');
        console.log('Order ID:', currentOrderId);
        console.log('Payment status:', paymentStatus);
        console.log('Current URL:', window.location.href);
        
        // ✅ MÉTHODE 1 : Détection via localStorage
        $('.btn-primary.wave').on('click', function(e) {
            console.log('🚀 Clic sur bouton Wave');
            localStorage.setItem('wave_order_id', currentOrderId);
            localStorage.setItem('wave_click_time', Date.now());
        });
        
        // Vérifier si on revient de Wave
        var storedOrderId = localStorage.getItem('wave_order_id');
        var clickTime = localStorage.getItem('wave_click_time');
        
        console.log('Stored Order ID:', storedOrderId);
        console.log('Click time:', clickTime);
        
        if (storedOrderId == currentOrderId && paymentStatus == 'pending') {
            var timeElapsed = Date.now() - parseInt(clickTime || 0);
            console.log('⏱️ Temps écoulé:', timeElapsed, 'ms');
            
            // Si plus de 2 secondes (temps minimum pour aller-retour Wave)
            if (timeElapsed > 2000) {
                console.log('✅ Retour détecté depuis Wave - Redirection...');
                
                // Nettoyer le storage
                localStorage.removeItem('wave_order_id');
                localStorage.removeItem('wave_click_time');
                
                // Rediriger vers payment=completed
                var newUrl = window.location.href.replace('payment=pending', 'payment=completed');
                
                console.log('🔄 Redirection vers:', newUrl);
                
                // Redirection immédiate
                window.location.replace(newUrl);
            }
        }
        
        // ✅ MÉTHODE 2 : Détection via focus/blur de la fenêtre
        var waveClicked = false;
        var focusLost = false;
        
        $('.btn-primary.wave').on('click', function(e) {
            waveClicked = true;
            console.log('🎯 Wave clicked flag set');
        });
        
        // Détecter quand l'utilisateur quitte la page
        $(window).on('blur', function() {
            if (waveClicked && paymentStatus == 'pending') {
                focusLost = true;
                console.log('👋 Focus perdu - User a quitté la page');
            }
        });
        
        // Détecter quand l'utilisateur revient
        $(window).on('focus', function() {
            if (waveClicked && focusLost && paymentStatus == 'pending') {
                console.log('👀 Focus regagné - User est revenu !');
                console.log('✅ Redirection automatique vers payment=completed');
                
                setTimeout(function() {
                    var newUrl = window.location.href.replace('payment=pending', 'payment=completed');
                    window.location.replace(newUrl);
                }, 1000); // Attendre 1 seconde
            }
        });
        
        console.log('==================');
    });
})(jQuery);
</script>
@endsection