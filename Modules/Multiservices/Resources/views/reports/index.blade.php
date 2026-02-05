@extends('layouts.app')

@section('title', 'Rapports Multiservices')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Rapports Multiservices
        <small>Statistiques et analyses</small>
    </h1>
</section>

<section class="content">
    <!-- Filtres -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="row">
                        <!-- Sélecteur de période -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('report.date_range'):</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-primary" id="daterange-btn">
                                        <span>
                                            <i class="fa fa-calendar"></i> @lang('messages.filter_by_date')
                                        </span>
                                        <i class="fa fa-caret-down"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="start_date" name="start_date" value="{{ $startDate ?? '' }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ $endDate ?? '' }}">
                            </div>
                        </div>

                        <!-- Location -->
                        @if(count($locations) > 1)
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('purchase.business_location'):</label>
                                <select name="location_id" id="location_filter" class="form-control select2">
                                    <option value="">@lang('lang_v1.all')</option>
                                    @foreach($locations as $id => $name)
                                    <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <!-- Boutons -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="button" class="btn btn-primary" id="filter_button">
                                    <i class="fa fa-filter"></i> Filtrer
                                </button>
                                <button type="button" class="btn btn-default" id="reset_button">
                                    Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Style Moderne -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="modern-info-box">
                <div class="modern-icon bg-aqua">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="modern-content">
                    <span class="modern-text" style="color: #000;">TRANSACTIONS</span>
                    <span class="modern-number" style="color: #000;">{{ $stats->total_transactions ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="modern-info-box">
                <div class="modern-icon bg-green">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="modern-content">
                    <span class="modern-text" style="color: #000;">VOLUME TOTAL</span>
                    <span class="modern-number" style="color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ number_format($stats->total_amount ?? 0, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="modern-info-box">
                <div class="modern-icon bg-orange">
                    <i class="fa fa-calculator"></i>
                </div>
                <div class="modern-content">
                    <span class="modern-text" style="color: #000;">FRAIS COLLECTÉS</span>
                    <span class="modern-number" style="color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ number_format($stats->total_fees ?? 0, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="modern-info-box">
                <div class="modern-icon bg-pink">
                    <i class="fa fa-trophy"></i>
                </div>
                <div class="modern-content">
                    <span class="modern-text" style="color: #000;">BÉNÉFICE NET</span>
                    <span class="modern-number" style="color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ number_format($stats->total_profit ?? 0, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux -->
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-mobile"></i> Par Opérateur</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr class="bg-gray">
                                <th>Opérateur</th>
                                <th class="text-right">Trans.</th>
                                <th class="text-right">Montant</th>
                                <th class="text-right">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byOperator as $item)
                            <tr>
                                <td>
                                    <span class="label" style="background: 
                                        {{ $item->operator == 'wave' ? '#01c38d' : 
                                           ($item->operator == 'orange_money' ? '#ff7900' : 
                                           ($item->operator == 'free_money' ? '#ed1c24' : '#6c757d')) }}; padding: 5px 10px;">
                                        {{ strtoupper(str_replace('_', ' ', $item->operator)) }}
                                    </span>
                                </td>
                                <td class="text-right"><strong>{{ $item->count }}</strong></td>
                                <td class="text-right">{{ number_format($item->total_amount, 0) }} FCFA</td>
                                <td class="text-right text-green"><strong>{{ number_format($item->total_profit, 0) }} FCFA</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="fa fa-info-circle"></i> Aucune donnée disponible
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-tag"></i> Par Type</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Type</th>
                                    <th class="text-right">Trans.</th>
                                    <th class="text-right">Montant</th>
                                    <th class="text-right">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byType as $item)
                                <tr>
                                    <td>
                                        <span class="label label-info" style="padding: 8px 12px; color: #000; font-size: 14px; font-weight: 600;">
                                            {{ $typeNames[$item->transaction_type] ?? $item->transaction_type }}
                                            {{ $item->transactionType ? $item->transactionType->name : 'Type inconnu' }}
                                    </td>
                                    <td class="text-right"><strong>{{ $item->count }}</strong></td>
                                    <td class="text-right" style="white-space: nowrap;">{{ number_format($item->total_amount, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-right text-green" style="white-space: nowrap;"><strong>{{ number_format($item->total_profit, 0, ',', ' ') }} FCFA</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fa fa-info-circle"></i> Aucune donnée disponible
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par Location -->
    @if(isset($byLocation) && $byLocation && count($byLocation) > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-map-marker"></i> Répartition par Location</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Location</th>
                                    <th class="text-right">Transactions</th>
                                    <th class="text-right">Montant Total</th>
                                    <th class="text-right">Frais</th>
                                    <th class="text-right">Bénéfice</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byLocation as $item)
                                <tr>
                                    <td>
                                        <i class="fa fa-building text-blue"></i>
                                        <strong>{{ $item->location ? $item->location->name : 'N/A' }}</strong>
                                    </td>
                                    <td class="text-right"><strong>{{ number_format($item->count) }}</strong></td>
                                    <td class="text-right">{{ number_format($item->total_amount, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-right">{{ number_format($item->total_fees, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-right text-green"><strong>{{ number_format($item->total_profit, 0, ',', ' ') }} FCFA</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>    
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistiques Détaillées -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bar-chart"></i> Statistiques Détaillées</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green"><i class="fa fa-check-circle"></i></span>
                                <h5 class="description-header">{{ $stats->completed_transactions ?? 0 }}</h5>
                                <span class="description-text">Transactions Complétées</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-yellow"><i class="fa fa-clock-o"></i></span>
                                <h5 class="description-header">{{ $stats->pending_transactions ?? 0 }}</h5>
                                <span class="description-text">En Attente</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-red"><i class="fa fa-times-circle"></i></span>
                                <h5 class="description-header">{{ $stats->canceled_transactions ?? 0 }}</h5>
                                <span class="description-text">Annulées</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="description-block">
                                <span class="description-percentage text-aqua"><i class="fa fa-percent"></i></span>
                                <h5 class="description-header">
                                    {{ $stats->total_transactions > 0 ? number_format(($stats->completed_transactions / $stats->total_transactions) * 100, 1) : 0 }}%
                                </h5>
                                <span class="description-text">Taux de Succès</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.modern-info-box {
    display: flex;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
    min-height: 100px;
}

.modern-icon {
    width: 90px;
    min-width: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: white;
    flex-shrink: 0;
}

.modern-content {
    flex: 1;
    padding: 15px 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}

.modern-text {
    font-size: 13px;
    color: #999;
    font-weight: 600;
    letter-spacing: 0.3px;
    margin-bottom: 5px;
    display: block;
}

.modern-number {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    display: block;
    line-height: 1.3;
}

.bg-aqua {
    background-color: #00c0ef !important;
}

.bg-green {
    background-color: #00a65a !important;
}

.bg-orange {
    background-color: #ff9800 !important;
}

.bg-pink {
    background-color: #e91e63 !important;
}

@media (max-width: 767px) {
    .modern-icon {
        width: 70px;
        min-width: 70px;
        font-size: 30px;
    }
}
</style>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    // Date range picker
    $('#daterange-btn').daterangepicker(
        dateRangeSettings,
        function(start, end) {
            $('#daterange-btn span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        }
    );
    
    // Initialiser avec les dates actuelles
    @if(!empty($startDate) && !empty($endDate))
        $('#daterange-btn span').html('{{ date("M d, Y", strtotime($startDate)) }} - {{ date("M d, Y", strtotime($endDate)) }}');
        $('#start_date').val('{{ $startDate }}');
        $('#end_date').val('{{ $endDate }}');
    @endif

    // Initialiser select2
    $('.select2').select2();

    // Bouton Filtrer
    $('#filter_button').click(function() {
        var start = $('#start_date').val();
        var end = $('#end_date').val();
        var location = $('#location_filter').val();
        
        var url = '{{ route("multiservices.reports.index") }}?start_date=' + start + '&end_date=' + end;
        if (location) {
            url += '&location_id=' + location;
        }
        window.location.href = url;
    });

    // Bouton Réinitialiser
    $('#reset_button').click(function() {
        window.location.href = '{{ route("multiservices.reports.index") }}';
    });
});
</script>
@endsection