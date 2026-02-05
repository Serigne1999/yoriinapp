@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0 tw-mb-14">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <div class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    
    @include('sale_pos.partials.keyboard_shortcuts')
    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
    
    {{-- NOUVEAU - Charger commande catalogue --}}
    @if(session()->has('catalogue_order_for_pos'))
    <script>
    $(document).ready(function() {
        var catalogueOrder = {!! json_encode(session('catalogue_order_for_pos')) !!};
        
        console.log('Commande catalogue détectée:', catalogueOrder);
        
        // Attendre que le POS soit complètement chargé
        setTimeout(function() {
            toastr.success('Chargement de la commande #' + catalogueOrder.order_id);
            
            // Charger chaque produit
            $.each(catalogueOrder.products, function(index, item) {
                console.log('Ajout produit:', item);
                
                // Chercher le produit et l'ajouter
                $.ajax({
                    url: '/pos/get_product_row/' + item.variation_id + '?product_id=' + item.product_id,
                    method: 'GET',
                    success: function(result) {
                        // Ajouter le produit au tableau
                        $('#pos_table tbody').append(result);
                        
                        // Mettre à jour la quantité
                        if(item.quantity > 1) {
                            $('#pos_table tbody tr').last().find('.pos_quantity').val(item.quantity).trigger('change');
                        }
                        
                        pos_total_row();
                    }
                });
            });
            
            // Pré-remplir les infos client si disponibles
            if(catalogueOrder.customer_name) {
                setTimeout(function() {
                    $('#customer_name_display').text(catalogueOrder.customer_name);
                    if(catalogueOrder.customer_phone) {
                        $('#customer_name_display').append(' (' + catalogueOrder.customer_phone + ')');
                    }
                }, 2000);
            }
        }, 1500);
    });
    </script>
    @php
        session()->forget('catalogue_order_for_pos');
    @endphp
    @endif
    <script>
    $(window).on('load', function() {
        setTimeout(function() {
            @if(session()->has('catalogue_order_for_pos'))
                let order = {!! json_encode(session('catalogue_order_for_pos')) !!};
                console.log('📦 Chargement commande:', order);
                
                toastr.info('Chargement de ' + order.products.length + ' produit(s)...');
                
                let delay = 1000;
                order.products.forEach(function(item, index) {
                    setTimeout(function() {
                        $('#search_product').val(item.variation_id).trigger('keyup');
                        
                        setTimeout(function() {
                            let $lastRow = $('#pos_table tbody tr:last');
                            if ($lastRow.length && item.quantity > 1) {
                                $lastRow.find('.pos_quantity').val(item.quantity).change();
                            }
                        }, 500);
                    }, delay);
                    delay += 1000;
                });
                
                setTimeout(function() {
                    toastr.success('Commande #' + order.order_id + ' chargée!');
                }, delay + 500);
            @endif
        }, 2000);
    });
    </script>

@php
    session()->forget('catalogue_order_for_pos');
@endphp
@endsection
