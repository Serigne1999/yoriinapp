@extends('layouts.app')
@section('title', __('Détails commande #') . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('content')
<section class="content-header">
    <h1>@lang('Commande') #{{str_pad($order->id, 6, '0', STR_PAD_LEFT)}}
        <small>{{date('d/m/Y H:i', strtotime($order->created_at))}}</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <!-- Informations client -->
        <div class="col-md-6">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('Informations client')])
                <table class="table table-striped">
                    <tr>
                        <th style="width: 40%">@lang('Nom'):</th>
                        <td>{{$order->customer_name}}</td>
                    </tr>
                    <tr>
                        <th>@lang('Téléphone'):</th>
                        <td>
                            <a href="https://wa.me/221{{preg_replace('/\D/', '', $order->customer_phone)}}" target="_blank">
                                <i class="fab fa-whatsapp" style="color: #25D366;"></i> {{$order->customer_phone}}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>@lang('Adresse'):</th>
                        <td>{{$order->customer_address}}</td>
                    </tr>
                    @if($order->customer_notes)
                    <tr>
                        <th>@lang('Notes'):</th>
                        <td>{{$order->customer_notes}}</td>
                    </tr>
                    @endif
                </table>
            @endcomponent
        </div>

        <!-- Statut et actions -->
        <div class="col-md-6">
            @component('components.widget', ['class' => 'box-success', 'title' => __('Statut et actions')])
                <div class="form-group">
                    <label>@lang('Statut actuel'):</label>
                    <select class="form-control" id="order_status">
                        <option value="pending" {{$order->status == 'pending' ? 'selected' : ''}}>@lang('En attente')</option>
                        <option value="paid" {{$order->status == 'paid' ? 'selected' : ''}}>@lang('Payé')</option>
                        <option value="processing" {{$order->status == 'processing' ? 'selected' : ''}}>@lang('En préparation')</option>
                        <option value="delivered" {{$order->status == 'delivered' ? 'selected' : ''}}>@lang('Livré')</option>
                        <option value="cancelled" {{$order->status == 'cancelled' ? 'selected' : ''}}>@lang('Annulé')</option>
                    </select>
                </div>
                <button type="button" class="btn btn-success btn-block" id="updateStatusBtn">
                    <i class="fa fa-save"></i> @lang('Mettre à jour le statut')
                </button>
                {{-- NOUVEAU - Bouton Traiter dans POS --}}
                <button type="button" class="btn btn-primary btn-block" id="processPOSBtn" style="margin-top: 10px;">
                    <i class="fa fa-cash-register"></i> Passé a la CAISSE
                </button>
                <hr>
                
                <a href="https://wa.me/221{{preg_replace('/\D/', '', $order->customer_phone)}}?text=Bonjour%20{{urlencode($order->customer_name)}},%20votre%20commande%20%23{{str_pad($order->id, 6, '0', STR_PAD_LEFT)}}%20est%20prête" 
                   class="btn btn-success btn-block" target="_blank">
                    <i class="fab fa-whatsapp"></i> @lang('Envoyer lien Wave via WhatsApp')
                </a>
            @endcomponent
        </div>
    </div>

    <!-- Produits commandés -->
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('Produits commandés')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Image')</th>
                                <th>@lang('Produit')</th>
                                <th>@lang('SKU')</th>
                                <th>@lang('Prix unitaire')</th>
                                <th>@lang('Quantité')</th>
                                <th>@lang('Sous-total')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $items = json_decode($order->items, true);
                                $total = 0;
                            @endphp
                            @foreach($items as $item)
                            @php
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            @endphp
                            <tr>
                                <td><img src="{{$item['image']}}" width="50" height="50" style="object-fit: cover;"></td>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['sku']}}</td>
                                <td><span class="display_currency" data-currency_symbol="true">{{$item['price']}}</span></td>
                                <td>{{$item['quantity']}}</td>
                                <td><span class="display_currency" data-currency_symbol="true">{{$subtotal}}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" style="text-align: right;">@lang('Total'):</th>
                                <th><span class="display_currency" data-currency_symbol="true">{{$order->total_amount}}</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> @lang('Retour à la liste')
            </a>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
    // Mettre à jour le statut
    $('#updateStatusBtn').click(function(){
        var status = $('#order_status').val();
        var url = "{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'updateOrderStatus'], $order->id)}}";
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: "{{csrf_token()}}",
                status: status
            },
            success: function(result){
                if(result.success){
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
});
// NOUVEAU - Traiter la commande dans le POS
$('#processPOSBtn').click(function(){
    var $btn = $(this);
    var originalHtml = $btn.html();
    
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Préparation...').prop('disabled', true);
    
    $.ajax({
        url: "{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'prepareOrderForPOS'], $order->id)}}",
        method: 'POST',
        data: {
            _token: "{{csrf_token()}}"
        },
        success: function(result){
            if(result.success){
                // Rediriger vers le POS
                window.location.href = result.pos_url;
            } else {
                toastr.error(result.msg);
                $btn.html(originalHtml).prop('disabled', false);
            }
        },
        error: function(){
            toastr.error('Erreur lors de la préparation');
            $btn.html(originalHtml).prop('disabled', false);
        }
    });
});
</script>
@endsection