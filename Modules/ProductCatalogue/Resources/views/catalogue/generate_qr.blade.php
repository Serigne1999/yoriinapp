@extends('layouts.app')
@section('title', __('productcatalogue::lang.catalogue_qr'))

@section('content')

<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 10px 15px;
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    margin-bottom: 15px;
    cursor: pointer;
    min-height: 100px;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.stats-card.green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card.orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stats-card.blue {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stats-card.purple {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.stats-icon {
    font-size: 20px;
    opacity: 0.8;
    margin-bottom: 8px;
}

.stats-number {
    font-size: 32px;
    font-weight: bold;
    margin: 8px 0;
}

.stats-label {
    font-size: 10px;
    opacity: 0.9;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('productcatalogue::lang.catalogue_qr')</h1>
</section>

<section class="content">
    <!-- Statistiques des commandes -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <a href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}" style="text-decoration: none;">
                <div class="stats-card green">
                    <div class="stats-icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number" id="total-orders">0</div>
                    <div class="stats-label">Total Commandes</div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}?status=pending" style="text-decoration: none;">
                <div class="stats-card orange">
                    <div class="stats-icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div class="stats-number" id="pending-orders">0</div>
                    <div class="stats-label">En Attente</div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}?status=processing" style="text-decoration: none;">
                <div class="stats-card blue">
                    <div class="stats-icon">
                        <i class="fa fa-spinner"></i>
                    </div>
                    <div class="stats-number" id="processing-orders">0</div>
                    <div class="stats-label">En Préparation</div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6">
            <a href="{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}?status=delivered" style="text-decoration: none;">
                <div class="stats-card purple">
                    <div class="stats-icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div class="stats-number" id="delivered-orders">0</div>
                    <div class="stats-label">Livrées</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="row">
        <div class="col-md-7">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location').':') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control', 'placeholder' => __('messages.please_select')]); !!}
                </div>
                <div class="form-group">
                    {!! Form::label('color', __('productcatalogue::lang.qr_code_color').':') !!}
                    {!! Form::text('color', '#000000', ['class' => 'form-control']); !!}
                </div>
                <div class="form-group">
                    {!! Form::label('title', __('productcatalogue::lang.title').':') !!}
                    {!! Form::text('title', $business->name, ['class' => 'form-control']); !!}
                </div>
                <div class="form-group">
                    {!! Form::label('subtitle', __('productcatalogue::lang.subtitle').':') !!}
                    {!! Form::text('subtitle', __('productcatalogue::lang.product_catalogue'), ['class' => 'form-control']); !!}
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('add_logo', 1, true, ['id' => 'show_logo', 'class' => 'input-icheck']); !!} @lang('productcatalogue::lang.show_business_logo_on_qrcode')
                        </label>
                    </div>
                </div>
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="generate_qr">@lang('productcatalogue::lang.generate_qr')</button>
            @endcomponent

            @component('components.widget', ['class' => 'box-solid'])
                <div class="row">
                    <div class="col-md-12">
                        <h4>@lang('productcatalogue::lang.setting'):</h4>
                        {!! Form::open(['url' => action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'productCatalogueSetting']), 'method' => 'post']) !!}
                            {!! Form::label('is_show', __('productcatalogue::lang.outofstock_products').':') !!}
                            <div class="form-inline">
                            <div class="form-group">
                            @php
                                $settings = json_decode($business->productcatalogue_settings);
                                $is_show = $settings->is_show ?? '';
                            @endphp
                                <div class="checkbox">
                                    <label>
                                        {!! Form::radio('is_show', 1, $is_show == 1 ? true : false, ['id' => 'show_logo', 'class' => 'input-icheck', 'required']); !!} @lang('productcatalogue::lang.show')
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        {!! Form::radio('is_show', 0, $is_show == 0 ? true : false, ['id' => 'show_logo', 'class' => 'input-icheck', 'required']); !!} @lang('productcatalogue::lang.hide')
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>
                                <strong>Lien de paiement Wave :</strong>
                            </label>
                            @php
                                $settings = json_decode($business->productcatalogue_settings, true);
                                $wave_payment_link = $settings['wave_payment_link'] ?? '';
                            @endphp
                            <input type="text" 
                                   name="wave_payment_link" 
                                   class="form-control" 
                                   placeholder="https://pay.wave.com/m/VOTRE_CODE/c/sn/?amount=10000"
                                   value="{{ $wave_payment_link }}">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> 
                                <strong>Format requis :</strong> Le lien doit contenir <code>?amount=XXXXX</code> à la fin.
                                <br>Le montant sera automatiquement remplacé par le total de chaque commande.
                                <br><strong>Exemple :</strong> https://pay.wave.com/m/M_sn_xt2UPAdHMxNG/c/sn/?amount=10000
                            </small>
                        </div>
                        {{-- ✅ NOUVEAU : Numéro WhatsApp de contact --}}
                        <div class="form-group">
                            <label>
                                <strong>Numéro WhatsApp de contact :</strong>
                                <span class="text-danger">*</span>
                            </label>
                            @php
                                $whatsapp_contact = $settings['whatsapp_contact'] ?? '';
                            @endphp
                            <input type="text" 
                                   name="whatsapp_contact" 
                                   class="form-control" 
                                   placeholder="77 123 45 67"
                                   value="{{ $whatsapp_contact }}"
                                   required>
                            <small class="text-muted">
                                <i class="fab fa-whatsapp text-success"></i> 
                                Les clients enverront leurs confirmations de commande sur ce numéro WhatsApp
                            </small>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>
                                <strong>Mode Restaurant :</strong>
                            </label>
                            <div class="checkbox">
                                @php
                                    $settings = json_decode($business->productcatalogue_settings, true);
                                    $restaurant_mode = $settings['restaurant_mode'] ?? false;
                                @endphp
                                <label>
                                    {!! Form::checkbox('restaurant_mode', 1, $restaurant_mode, ['class' => 'input-icheck']); !!} 
                                    Activer le mode Restaurant (commandes sur table sans adresse de livraison)
                                </label>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white" id="">@lang('productcatalogue::lang.save')</button>
                        {{-- Bouton QR Codes Tables Restaurant --}}
                        <a href="{{ url('product-catalogue/restaurant-tables') }}" 
                           class="tw-dw-btn tw-dw-btn-success tw-dw-btn-sm tw-text-white" 
                           style="margin-left: 10px;">
                            <i class="fa fa-qrcode"></i> QR Codes Tables Restaurant
                        </a>
                        {!! Form::close() !!}
                    </div>
                </div>
            @endcomponent

            @component('components.widget', ['class' => 'box-solid'])
                <div class="row">
                    <div class="col-md-12">
                        <strong>@lang('lang_v1.instruction'):</strong>
                        <table class="table table-striped">
                            <tr>
                                <td>1</td>
                                <td>@lang('productcatalogue::lang.catalogue_instruction_1')</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>@lang('productcatalogue::lang.catalogue_instruction_2')</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>@lang('productcatalogue::lang.catalogue_instruction_3')</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endcomponent
        </div>
        
        <div class="col-md-5">
            @component('components.widget', ['class' => 'box-solid'])
                <div class="text-center">
                    <div id="qrcode"></div>
                    <span id="catalogue_link"></span>
                    <br>
                    <a href="#" class="tw-dw-btn tw-dw-btn-success tw-text-white hide" id="download_image">@lang('productcatalogue::lang.download_image')</a>
                </div>
            @endcomponent
        </div>
    </div>
</section>

@stop

@section('javascript')
<script src="{{ asset('modules/productcatalogue/plugins/easy.qrcode.min.js') }}"></script>
<script type="text/javascript">
    (function($) {
        "use strict";

    $(document).ready(function(){
        $('#color').colorpicker();

        // Charger les statistiques des commandes
        loadOrderStats();
    });

    // Fonction pour charger les statistiques
    function loadOrderStats() {
        $.ajax({
            url: "{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}",
            method: 'GET',
            data: { get_stats: true },
            success: function(data) {
                if(data.stats) {
                    animateNumber($('#total-orders'), data.stats.total || 0);
                    animateNumber($('#pending-orders'), data.stats.pending || 0);
                    animateNumber($('#processing-orders'), data.stats.processing || 0);
                    animateNumber($('#delivered-orders'), data.stats.delivered || 0);
                }
            }
        });
    }

    // Animation des chiffres
    function animateNumber($element, target) {
        $({ counter: 0 }).animate({ counter: target }, {
            duration: 1000,
            easing: 'swing',
            step: function() {
                $element.text(Math.ceil(this.counter));
            }
        });
    }
    
    // Génération du QR Code (code original)
    $(document).on('click', '#generate_qr', function(e){
        $('#qrcode').html('');
        if ($('#location_id').val()) {
            var link = "{{url('catalogue/' . session('business.id'))}}/" + $('#location_id').val();
            var color = '#000000';
            if ($('#color').val().trim() != '') {
                color = $('#color').val();
            }
            var opts = {
                text: link,
                margin: 4,
                width: 256,
                height: 256,
                quietZone: 20,
                colorDark: color,
                colorLight: "#ffffffff", 
            }

            if ($('#title').val().trim() !== '') {
                opts.title = $('#title').val();
                opts.titleFont = "bold 18px Arial";
                opts.titleColor = "#004284";
                opts.titleBackgroundColor = "#ffffff";
                opts.titleHeight = 60;
                opts.titleTop = 20;
            }

            if ($('#subtitle').val().trim() !== '') {
                opts.subTitle = $('#subtitle').val();
                opts.subTitleFont = "14px Arial";
                opts.subTitleColor = "#4F4F4F";
                opts.subTitleTop = 40;
            }

            if ($('#show_logo').is(':checked')) {
                opts.logo = "{{asset('uploads/business_logos/' . $business->logo)}}";
            }

            new QRCode(document.getElementById("qrcode"), opts);
            $('#catalogue_link').html('<a target="_blank" href="'+ link +'">Link</a>');
            $('#download_image').removeClass('hide');
            $('#qrcode').find('canvas').attr('id', 'qr_canvas')
        } else {
            alert("{{__('productcatalogue::lang.select_business_location')}}")
        }
    });

    // Téléchargement du QR Code
    $('#download_image').click(function(e) {
        e.preventDefault();
        var link = document.createElement('a');
        link.download = 'qrcode.png';
        link.href = document.getElementById('qr_canvas').toDataURL()
        link.click();
    });

    })(jQuery);
</script>
@endsection