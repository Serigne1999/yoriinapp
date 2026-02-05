@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html class="tw-bg-white tw-scroll-smooth" lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="VD3rwPoGGAaJzUfVqPSrsz6n3Ib22kx1yxmJ9r1hin0" />
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
    <meta name="theme-color" content="#3c8dbc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="YoriinApp">
    
    <!-- Préchargement -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Logo -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/logo-small.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/logo-small.png') }}">
    <link rel="icon" href="{{ asset('img/logo-small.png?v=2') }}">
    
    <title>@yield('title') - {{ Session::get('business.name') }}</title>

    @include('layouts.partials.css')
    @include('layouts.partials.extracss')
    @yield('css')
</head>

<body class="tw-font-sans tw-antialiased tw-text-gray-900 tw-bg-gray-100 @if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif">
    
    @if(isset($isDemoUser) && $isDemoUser)
        <div style="background-color:#007bff;color:white;padding:10px;text-align:center;">
            🧪 Vous êtes actuellement sur la version <strong>démo</strong> de l'application. Toutes les données seront automatiquement réinitialisées.
        </div>
    @endif

    <div class="tw-flex thetop">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>

        @if (!$pos_layout && $request->segment(1) != 'customer-display')
            @include('layouts.partials.sidebar')
        @endif

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Currency fields -->
        <input type="hidden" id="__code" value="{{ session('currency') && isset(session('currency')['code']) ? session('currency')['code'] : '' }}">
        <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] ?? '' }}">
        <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] ?? ',' }}">
        <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] ?? ''}}">
        <input type="hidden" id="__symbol_placement" value="{{ session('business.currency_symbol_placement') ?? ''}}">
        <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
        <input type="hidden" id="__quantity_precision" value="{{ session('business.quantity_precision', 2) }}">

        @can('view_export_buttons')
            <input type="hidden" id="view_export_buttons">
        @endcan

        @if (isMobile())
            <input type="hidden" id="__is_mobile">
        @endif

        @if (session('status'))
            <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
        @endif

        <main class="tw-flex tw-flex-col tw-flex-1 tw-h-full tw-min-w-0 tw-bg-gray-100">
            @if($request->segment(1) != 'customer-display' && !$pos_layout)
                @include('layouts.partials.header')
            @elseif($request->segment(1) != 'customer-display')
                @include('layouts.partials.header-pos')
            @endif

            <div id="app">
                @yield('vue')
            </div>

            <div class="tw-flex-1 tw-overflow-y-auto tw-h-screen" id="scrollable-container">
                @yield('content')

                @if (!$pos_layout)
                    @include('layouts.partials.footer')
                @else
                    @include('layouts.partials.footer_pos')
                @endif
            </div>

            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <section class="invoice print_section" id="receipt_section"></section>
        </main>

        @include('home.todays_profit_modal')

        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>

        @if (!empty($__additional_html))
            {!! $__additional_html !!}
        @endif

        @include('layouts.partials.javascripts')

        <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

        @if (!empty($__additional_views) && is_array($__additional_views))
            @foreach ($__additional_views as $additional_view)
                @includeIf($additional_view)
            @endforeach
        @endif

        <div class="overlay tw-hidden"></div>
    </div>

    <style>
    @media print {
        #scrollable-container {
            overflow: visible !important;
            height: auto !important;
        }
    }
    .small-view-side-active {
        display: grid !important;
        z-index: 1000;
        position: absolute;
    }
    .overlay {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.8);
        position: fixed;
        top: 0;
        left: 0;
        display: none;
        z-index: 20;
    }
    .tw-dw-btn.tw-dw-btn-xs.tw-dw-btn-outline {
        width: max-content;
        margin: 2px;
    }
    #scrollable-container {
        position: relative;
    }
    </style>

    <!-- Notifications nouvelles commandes -->
    <script>
    (function() {
        let lastOrderId = {{ DB::table('catalogue_orders')->where('business_id', session('business.id'))->max('id') ?? 0 }};
        console.log('🔔 Système de notification initialisé. Dernier ID:', lastOrderId);
        
        function checkNewOrders() {
            $.ajax({
                url: '{{ route("catalogue.check_new_orders") }}',
                method: 'GET',
                data: { last_order_id: lastOrderId },
                success: function(response) {
                    if (response.new_orders && response.new_orders.length > 0) {
                        console.log('🛒 Nouvelles commandes détectées:', response.new_orders);
                        
                        try {
                            let audio = new Audio('{{ asset("sounds/notification.mp3") }}');
                            audio.volume = 0.5;
                            audio.play().catch(e => console.log('Lecture audio bloquée:', e));
                        } catch(e) {
                            console.log('Erreur audio:', e);
                        }
                        
                        response.new_orders.forEach(function(order) {
                            toastr.success(
                                '<strong>Commande #' + order.id + '</strong><br>' +
                                'Client : ' + order.customer_name + '<br>' +
                                'Montant : ' + parseFloat(order.total).toFixed(0) + ' FCFA<br>' +
                                '<a href="{{ url("product-catalogue/orders") }}/' + order.id + '" class="btn btn-sm btn-info" style="margin-top:5px; color:white;">Voir la commande</a>',
                                '🛒 Nouvelle commande !',
                                { 
                                    timeOut: 15000,
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    enableHtml: true
                                }
                            );
                        });
                        
                        let $badge = $('#catalogue-orders-badge');
                        if ($badge.length) {
                            let currentCount = parseInt($badge.text()) || 0;
                            let newCount = currentCount + response.new_orders.length;
                            $badge.text(newCount).show();
                        }
                        
                        lastOrderId = response.latest_order_id;
                    }
                },
                error: function(xhr) {
                    console.log('Erreur vérification commandes:', xhr);
                }
            });
        }
        
        setInterval(checkNewOrders, 30000);
        setTimeout(checkNewOrders, 5000);
    })();
    </script>

    //<!-- PWA Service Worker -->
    
    <script>
    /*
    if ("serviceWorker" in navigator) {
        window.addEventListener("load", function() {
            navigator.serviceWorker.register("/sw-pos.js")
                .then(reg => {
                    console.log("✅ Service Worker enregistré");
                    
                    // Background Sync support
                    if ('sync' in reg) {
                        console.log("✅ Background Sync disponible");
                    }
                })
                .catch(err => console.error("❌ Erreur Service Worker:", err));
        });
    }
    */
    </script>
    <script src="{{ asset('js/pos-db.js?v=2') }}"></script>
    <script src="{{ asset('js/pos-sync.js?v=2') }}"></script>
    <!-- Fix Dropdown Action -->
    <script>
    $(document).ready(function() {
        console.log('🔧 Initializing dropdowns...');
        
        setTimeout(function() {
            $('.dropdown-toggle').dropdown('dispose');
            $('.dropdown-toggle').dropdown();
            
            $('body').off('click', '.dropdown-toggle');
            $('body').on('click', '.dropdown-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $btn = $(this);
                var $menu = $btn.next('.dropdown-menu');
                
                $('.dropdown-menu').not($menu).removeClass('show');
                $menu.toggleClass('show');
                
                console.log('✅ Dropdown toggled');
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.btn-group').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });
            
            console.log('✅ ' + $('.dropdown-toggle').length + ' dropdowns ready');
        }, 500);
    });
    </script>

    <!-- Indicateur connexion -->
    <script>
    function updateOnlineStatus() {
        const indicator = document.getElementById('offline-indicator');
        if (indicator) {
            indicator.style.display = navigator.onLine ? 'none' : 'block';
        }
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    window.addEventListener('load', updateOnlineStatus);
    </script>
    <!-- Update offline indicator -->
    <script>
    function updateConnectionStatus() {
        const icon = document.getElementById('connection-icon');
        const text = document.getElementById('connection-text');
        
        if (navigator.onLine) {
            if (icon) icon.className = 'fa fa-wifi';
            if (text) text.textContent = 'En ligne';
            if (icon) icon.style.color = '#00a65a';
        } else {
            if (icon) icon.className = 'fa fa-wifi';
            if (text) text.textContent = 'Hors ligne';
            if (icon) icon.style.color = '#dd4b39';
        }
    }
    
    // Mise à jour du compteur de produits cachés
    window.addEventListener('products-synced', function(e) {
        const badge = document.getElementById('cached-products-count');
        if (badge) {
            badge.textContent = e.detail.count;
            badge.style.display = 'inline-block';
        }
    });
    
    // Afficher le compteur au chargement
    setTimeout(function() {
        const productCount = localStorage.getItem('product_count');
        if (productCount && parseInt(productCount) > 0) {
            const badge = document.getElementById('cached-products-count');
            if (badge) {
                badge.textContent = productCount;
                badge.style.display = 'inline-block';
            }
        }
    }, 1000);
    
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    window.addEventListener('load', updateConnectionStatus);
    </script>
</body>
</html>
