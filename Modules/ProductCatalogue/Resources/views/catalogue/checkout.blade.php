@extends('layouts.guest')
@section('title', 'Finaliser ma commande - ' . $business->name)

@section('content')
<style>
.btn-wave {
    background: #0066FF !important; /* Bleu au lieu d'orange */
}

.btn-wave:hover {
    background: #0052CC !important; /* Bleu plus foncé au hover */
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 102, 255, 0.4) !important;
}

.btn-wave:active {
    transform: translateY(0);
}

.btn-wave:disabled {
    background: #ccc !important;
    cursor: not-allowed !important;
    transform: none !important;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.checkout-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
}

.checkout-header {
    text-align: center;
    margin-bottom: 30px;
}

.checkout-header h2 {
    color: #28a745;
    margin-bottom: 10px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
}

.checkout-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #28a745;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #28a745;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.required {
    color: #dc3545;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-weight: bold;
    margin-bottom: 5px;
}

.order-item-price {
    color: #28a745;
    font-size: 14px;
}

.order-item-quantity {
    color: #666;
    font-size: 14px;
}

.order-summary {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 14px;
}

.summary-row.total {
    font-size: 20px;
    font-weight: bold;
    color: #28a745;
    border-top: 2px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
}

.btn-submit-order {
    width: 100%;
    background-color: #28a745;
    color: white;
    border: none;
    padding: 15px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 20px;
    transition: all 0.3s;
}

.btn-submit-order:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-submit-order:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
    transform: none;
}

.btn-back {
    display: inline-block;
    color: #6c757d;
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: bold;
}

.btn-back:hover {
    color: #28a745;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.empty-cart-message {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart-message i {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-cart-message h3 {
    color: #999;
    margin-bottom: 20px;
}

.btn-continue-shopping {
    display: inline-block;
    background-color: #28a745;
    color: white;
    padding: 12px 30px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.btn-continue-shopping:hover {
    background-color: #218838;
    color: white;
}
</style>

<div class="checkout-container">
    <a href="{{url('/catalogue/' . $business->id . '/' . $business_location->id)}}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour au catalogue
    </a>

    <div class="checkout-header">
        <h2><i class="fas fa-shopping-bag"></i> Finaliser ma commande</h2>
        <p>{{$business->name}} - {{$business_location->name}}</p>
    </div>

    @if(count($cart) == 0)
        <div class="empty-cart-message">
            <i class="fas fa-shopping-cart"></i>
            <h3>Votre panier est vide</h3>
            <a href="{{url('/catalogue/' . $business->id . '/' . $business_location->id)}}" class="btn-continue-shopping">
                <i class="fas fa-shopping-bag"></i> Continuer mes achats
            </a>
        </div>
    @else
        <form id="checkoutForm" method="POST" action="{{url('/catalogue/' . $business->id . '/' . $business_location->id . '/order/submit')}}">
            @csrf
            <div class="checkout-grid">
                <!-- Formulaire client - Section intelligente -->
                <div class="checkout-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> 
                        @if($is_restaurant_mode && !empty($table))
                            Vos informations
                        @else
                            Informations de livraison
                        @endif
                    </h3>
                    
                    {{-- NOUVEAU - Alerte Mode Restaurant --}}
                    @if($is_restaurant_mode && !empty($table))
                        <div class="alert" style="background: #d4edda; border: 2px solid #28a745; padding: 20px; border-radius: 10px; margin-bottom: 25px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <i class="fas fa-utensils" style="font-size: 32px; color: #28a745;"></i>
                                <div>
                                    <h4 style="margin: 0 0 5px 0; color: #155724; font-size: 18px;">
                                        <strong>Commande sur place</strong>
                                    </h4>
                                    <p style="margin: 0; color: #155724;">
                                        Vous commandez depuis la <strong>Table {{$table->name}}</strong>
                                        @if($table->description)
                                            <br><small>{{$table->description}}</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="table_id" value="{{$table->id}}">
                        <input type="hidden" name="table_number" value="{{$table->name}}">
                    @endif
                    
                    <div class="form-group">
                        <label for="customer_name">Nom complet <span class="required">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required placeholder="Ex: Amadou Diallo">
                    </div>
                
                    <div class="form-group">
                        <label for="customer_phone">
                            Numéro @if(!$is_restaurant_mode || empty($table))WhatsApp @else de téléphone @endif
                            <span class="required">*</span>
                        </label>
                        <input type="tel" id="customer_phone" name="customer_phone" required placeholder="Ex: 77 123 45 67">
                    </div>
                
                    {{-- Adresse uniquement pour mode e-commerce --}}
                    @if(!$is_restaurant_mode || empty($table))
                        <div class="form-group">
                            <label for="customer_address">Adresse de livraison <span class="required">*</span></label>
                            <textarea id="customer_address" name="customer_address" required placeholder="Ex: Parcelles Assainies, Unité 10, Villa 123"></textarea>
                        </div>
                    @endif
                
                    <div class="form-group">
                        <label for="customer_notes">
                            @if($is_restaurant_mode && !empty($table))
                                Instructions spéciales (optionnel)
                            @else
                                Instructions de livraison (optionnel)
                            @endif
                        </label>
                        <textarea id="customer_notes" name="customer_notes" placeholder="@if($is_restaurant_mode && !empty($table))Ex: Sans oignons, bien cuit...@else Ex: Appeler en arrivant, code portail: 1234 @endif"></textarea>
                    </div>
                </div>

                <!-- Récapitulatif commande -->
                <div class="checkout-section">
                    <h3 class="section-title"><i class="fas fa-list"></i> Votre commande</h3>
                    
                    <div id="orderItems">
                        @php
                            $total = 0;
                        @endphp
                        @foreach($cart as $key => $item)
                            @php
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            @endphp
                            <div class="order-item">
                                <img src="{{$item['image']}}" alt="{{$item['name']}}" class="order-item-image">
                                <div class="order-item-details">
                                    <div class="order-item-name">{{$item['name']}}</div>
                                    <div class="order-item-quantity">Quantité: {{$item['quantity']}}</div>
                                    <div class="order-item-price">
                                        <span class="display_currency" data-currency_symbol="true">{{$item['price']}}</span>
                                        x {{$item['quantity']}} = 
                                        <span class="display_currency" data-currency_symbol="true">{{$subtotal}}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Sous-total:</span>
                            <span class="display_currency" data-currency_symbol="true">{{$total}}</span>
                        </div>
                        <div class="summary-row">
                            <span>Frais de livraison:</span>
                            <span>À définir</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span class="display_currency" data-currency_symbol="true">{{$total}}</span>
                        </div>
                    </div>
                    {{-- SECTION PAIEMENT --}}
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
                        <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">
                            <i class="fas fa-credit-card"></i> Mode de paiement
                        </h4>
                        
                        @php
                            $settings = json_decode($business->productcatalogue_settings, true);
                            $wave_link = $settings['wave_payment_link'] ?? '';
                        @endphp
                        
                        {{-- Option 1 : Paiement Wave (si configuré) --}}
                        @if(!empty($wave_link))
                            @php
                                // Remplacer le montant dans le lien Wave avec le vrai total
                                $wave_payment_url = preg_replace('/amount=\d+/', 'amount=' . intval($total), $wave_link);
                            @endphp
                            
                            <input type="hidden" id="wave_payment_url" value="{{ $wave_payment_url }}">
                            <input type="hidden" id="cart_total" value="{{ $total }}">
                            
                            <div class="payment-option" style="margin-bottom: 15px;">
                                <button type="button"
                                   id="wavePaymentBtn"
                                   class="btn-wave"
                                   style="display: block; width: 100%; background: #0066FF !important; color: white !important; border: none; padding: 15px; border-radius: 8px; text-decoration: none; text-align: center; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0, 102, 255, 0.3); cursor: pointer;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                        <i class="fas fa-mobile-alt" style="font-size: 24px;"></i>
                                        <div style="text-align: left;">
                                            <div style="font-size: 16px; font-weight: bold;">Payer avec Wave</div>
                                            <div style="font-size: 14px; opacity: 0.9;">
                                                <span class="display_currency" data-currency_symbol="true">{{$total}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                                <p style="text-align: center; margin: 8px 0 0 0; font-size: 12px; color: #666;">
                                    <i class="fas fa-shield-alt"></i> Paiement sécurisé • Instantané
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin: 15px 0; color: #999; font-weight: bold;">
                                - OU -
                            </div>
                        @endif
                        
                        {{-- Option 2 : Paiement à la livraison / sur place --}}
                        <div class="payment-option">
                            <button type="submit" class="btn-submit-order" id="submitOrderBtn"
                                    style="width: 100%; background-color: #28a745; color: white; border: none; padding: 15px; font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; transition: all 0.3s;">
                                <i class="fas fa-money-bill-wave"></i> 
                                @if($is_restaurant_mode && !empty($table))
                                    Payer sur place
                                @else
                                    Payer à la livraison
                                @endif
                                <div style="font-size: 14px; margin-top: 5px; opacity: 0.9;">
                                    <span class="display_currency" data-currency_symbol="true">{{$total}}</span>
                                </div>
                            </button>
                            <p style="text-align: center; margin: 8px 0 0 0; font-size: 12px; color: #666;">
                                <i class="fas fa-handshake"></i> Paiement en espèces
                                @if(!$is_restaurant_mode || empty($table))
                                    au moment de la livraison
                                @else
                                    au moment du service
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    {{-- Info supplémentaire --}}
                    @if(empty($wave_link))
                        <div style="text-align: center; margin-top: 15px; font-size: 13px; color: #666;">
                            <i class="fab fa-whatsapp" style="color: #25D366;"></i> 
                            Vous recevrez une confirmation par WhatsApp
                        </div>
                    @endif
                    <!--<button type="submit" class="btn-submit-order" id="submitOrderBtn">
                    <!--    <i class="fas fa-check-circle"></i> Confirmer ma commande
                    <!--</button>

                    <!--<div style="text-align: center; margin-top: 15px; font-size: 13px; color: #666;">
                    <!--    <i class="fab fa-whatsapp" style="color: #25D366;"></i> 
                    <!--    Vous recevrez le lien de paiement Wave par WhatsApp
                    </div>-->
                </div>
            </div>
        </form>
    @endif
</div>

<!-- Add currency related field-->
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
        // Configuration des devises
        __currency_symbol = $('input#__symbol').val();
        __currency_thousand_separator = $('input#__thousand').val();
        __currency_decimal_separator = $('input#__decimal').val();
        __currency_symbol_placement = $('input#__symbol_placement').val();
        __currency_precision = $('input#__precision').length > 0 ? $('input#__precision').val() : 2;
        __quantity_precision = $('input#__quantity_precision').length > 0 ? $('input#__quantity_precision').val() : 2;

        __currency_convert_recursively($('.checkout-container'));

        var isRestaurantMode = {{ ($is_restaurant_mode && !empty($table)) ? 'true' : 'false' }};
        var paymentButtonText = isRestaurantMode ? 'Payer sur place' : 'Payer à la livraison';

        // =====================================
        // GESTIONNAIRE BOUTON WAVE
        // =====================================
        $('#wavePaymentBtn').on('click', function(e) {
            e.preventDefault();
            
            var name = $('#customer_name').val().trim();
            var phone = $('#customer_phone').val().trim();
            var address = isRestaurantMode ? 'Table' : $('#customer_address').val().trim();
            
            if (!name || !phone || (!isRestaurantMode && !address)) {
                alert('Veuillez remplir tous les champs obligatoires avant de payer');
                return false;
            }
            
            var $btn = $(this);
            var originalHtml = $btn.html();
            
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...').prop('disabled', true);
            
            $.ajax({
                url: $('#checkoutForm').attr('action'),
                method: 'POST',
                data: $('#checkoutForm').serialize() + '&payment_method=wave',
                success: function(response) {
                    if (response.success) {
                        // ✅ Rediriger vers la page de confirmation avec le lien Wave
                        // La page de confirmation affichera le bouton pour payer via Wave
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Erreur lors de l\'enregistrement');
                        $btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    alert('Erreur lors de l\'enregistrement de la commande');
                    console.log(xhr.responseText);
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        });
        // =====================================
        // GESTIONNAIRE PAIEMENT À LA LIVRAISON
        // =====================================
        $('#checkoutForm').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $btn = $('#submitOrderBtn');
            
            var name = $('#customer_name').val().trim();
            var phone = $('#customer_phone').val().trim();
            var address = isRestaurantMode ? 'Table' : $('#customer_address').val().trim();
            
            if (!name || !phone || (!isRestaurantMode && !address)) {
                alert('Veuillez remplir tous les champs obligatoires');
                return false;
            }
            
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Envoi en cours...').prop('disabled', true);
            
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize() + '&payment_method=cash',
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Une erreur est survenue');
                        $btn.html('<i class="fas fa-money-bill-wave"></i> ' + paymentButtonText).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    alert('Erreur lors de l\'enregistrement de la commande');
                    console.log(xhr.responseText);
                    $btn.html('<i class="fas fa-money-bill-wave"></i> ' + paymentButtonText).prop('disabled', false);
                }
            });
        });
    });
})(jQuery);
</script>
@endsection