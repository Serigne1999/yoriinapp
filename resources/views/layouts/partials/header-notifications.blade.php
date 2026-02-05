@php
    $all_notifications = auth()->user()->notifications;
    $unread_notifications = $all_notifications->where('read_at', null);
    $total_unread = count($unread_notifications);
@endphp
<!-- Notifications: style can be found in dropdown.less -->
<li class="dropdown notifications-menu tw-list-none">
    <a type="button"
        class="dropdown-toggle load_notifications tw-inline-flex tw-items-center tw-ring-1 tw-ring-white/10 tw-justify-center tw-text-sm tw-font-medium tw-text-white hover:tw-text-white tw-transition-all tw-duration-200 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-p-1.5 tw-rounded-lg"
        data-toggle="dropdown" id="show_unread_notifications" data-loaded="false">
        <span class="tw-sr-only">
            Notifications
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
        </svg>
        <span class="label label-warning notifications_count">@if (!empty($total_unread)){{$total_unread}}@endif</span>
    </a>
    <ul class="dropdown-menu !tw-p-2 !tw-w-80 tw-absolute !tw-right-0 !tw-z-10 !tw-mt-2 !tw-origin-top-right !tw-bg-white !tw-rounded-lg !tw-shadow-lg !tw-ring-1 !tw-ring-gray-200 !focus:tw-outline-none" style="left: auto !important ; height:90vh; overflow-y: scroll;">
        <!-- <li class="header">You have 10 unread notifications</li> -->
        <li>
            <!-- inner menu: contains the actual data -->

            <ul class="menu" id="notifications_list">
            </ul>
        </li>

        @if (count($all_notifications) > 10)
            <li class="footer load_more_li">
                <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
            </li>
        @endif
    </ul>
</li>

<input type="hidden" id="notification_page" value="1">
{{-- BADGE COMMANDES CATALOGUE --}}
@php
    $pending_catalogue_orders = DB::table('catalogue_orders')
        ->where('business_id', session('business.id'))
        ->where('status', 'pending')
        ->count();
@endphp

<li class="dropdown notifications-menu catalogue-orders-menu tw-list-none">
    <a type="button"
        class="dropdown-toggle tw-inline-flex tw-items-center tw-ring-1 tw-ring-white/10 tw-justify-center tw-text-sm tw-font-medium tw-text-white hover:tw-text-white tw-transition-all tw-duration-200 tw-bg-success-800 hover:tw-bg-success-700 tw-p-1.5 tw-rounded-lg"
        data-toggle="dropdown">
        <span class="tw-sr-only">
            Commandes Catalogue
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
            <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
            <path d="M17 17h-11v-14h-2" />
            <path d="M6 5l14 1l-1 7h-13" />
        </svg>
        <span class="label label-success" id="catalogue-orders-badge" @if($pending_catalogue_orders == 0) style="display:none;" @endif>
            {{ $pending_catalogue_orders }}
        </span>
    </a>
    <ul class="dropdown-menu !tw-p-2 !tw-w-80 tw-absolute !tw-right-0 !tw-z-10 !tw-mt-2 !tw-origin-top-right !tw-bg-white !tw-rounded-lg !tw-shadow-lg !tw-ring-1 !tw-ring-gray-200 !focus:tw-outline-none" style="left: auto !important; max-height:400px; overflow-y: auto;">
        <li class="header tw-p-2 tw-font-bold tw-text-gray-700 tw-border-b">
            {{ $pending_catalogue_orders }} commande(s) en attente
        </li>
        <li>
            <ul class="menu">
                @php
                    $recent_orders = DB::table('catalogue_orders')
                        ->where('business_id', session('business.id'))
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse($recent_orders as $order)
                    <li class="tw-border-b tw-border-gray-100 last:tw-border-0">
                        <a href="{{ url('product-catalogue/orders/' . $order->id) }}"
                           class="tw-block tw-p-3 hover:tw-bg-gray-50 tw-transition">
                            <div class="tw-flex tw-items-center tw-gap-3">
                                <div class="tw-flex-shrink-0">
                                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-full tw-bg-green-100 tw-text-green-600">
                                        <i class="fa fa-shopping-cart"></i>
                                    </span>
                                </div>
                                <div class="tw-flex-1 tw-min-w-0">
                                    <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                                        Commande #{{ $order->id }}
                                    </p>
                                    <p class="tw-text-xs tw-text-gray-500">
                                        {{ $order->customer_name }}
                                    </p>
                                    <p class="tw-text-xs tw-font-semibold tw-text-green-600">
                                        {{ number_format($order->total ?? 0, 0) }} FCFA                                    
                                    </p>
                                </div>
                                <div class="tw-flex-shrink-0">
                                    <i class="fa fa-chevron-right tw-text-gray-400"></i>
                                </div>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="tw-p-4 tw-text-center tw-text-gray-500">
                        Aucune commande en attente
                    </li>
                @endforelse
            </ul>
        </li>
        <li class="footer tw-border-t tw-p-2">
            <a href="{{ url('product-catalogue/orders') }}" 
               class="tw-block tw-text-center tw-text-sm tw-font-medium tw-text-blue-600 hover:tw-text-blue-800">
                Voir toutes les commandes →
            </a>
        </li>
    </ul>
</li>