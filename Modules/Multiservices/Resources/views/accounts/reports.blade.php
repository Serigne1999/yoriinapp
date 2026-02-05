@extends('layouts.app')

@section('title', 'Rapports des Comptes')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Rapports des Comptes Opérateurs
        <small>Vue d'ensemble et statistiques</small>
    </h1>
</section>

<section class="content">
    <!-- Filtres -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
                    <form method="GET" action="{{ route('multiservices.accounts.reports') }}" class="form-inline">
                        <div class="form-group">
                            <label>Du :</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group" style="margin-left: 10px;">
                            <label>Au :</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}" required>
                        </div>
                        
                        <div class="form-group" style="margin-left: 20px;">
                            <label>Location :</label>
                            <select name="location_id" class="form-control" style="min-width: 200px;">
                                <option value="">Toutes les locations</option>
                                @foreach(\App\BusinessLocation::where('business_id', auth()->user()->business_id)->get() as $location)
                                <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="margin-left: 10px;">
                            <i class="fa fa-filter"></i> Filtrer
                        </button>

                        <a href="{{ route('multiservices.accounts.reports') }}" class="btn btn-default">
                            <i class="fa fa-refresh"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Solde Total</span>
                    <span class="info-box-number" style="color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ number_format($stats['total_balance'], 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Dépôts (période)</span>
                    <span class="info-box-number" style="color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ number_format($stats['total_deposits'], 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Retraits (période)</span>
                    <span class="info-box-number" style="color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ number_format($stats['total_withdrawals'], 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Comptes Actifs</span>
                    <span class="info-box-number" style="color: #fff;">{{ $stats['total_accounts'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Par Opérateur -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-mobile"></i> Répartition par Opérateur</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Opérateur</th>
                                    <th class="text-center">Nombre de comptes</th>
                                    <th class="text-right">Solde total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byOperator as $item)
                                <tr>
                                    <td>
                                        @if($item->operator == 'wave')
                                            <span class="label" style="background: #01c38d; padding: 5px 10px;">WAVE</span>
                                        @elseif($item->operator == 'orange_money')
                                            <span class="label" style="background: #ff7900; padding: 5px 10px;">ORANGE MONEY</span>
                                        @elseif($item->operator == 'free_money')
                                            <span class="label" style="background: #ed1c24; padding: 5px 10px;">FREE MONEY</span>
                                        @else
                                            <span class="label label-default" style="padding: 5px 10px;">{{ strtoupper($item->operator) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center"><strong>{{ $item->count }}</strong></td>
                                    <td class="text-right" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <strong class="text-green">{{ number_format($item->total_balance, 0, ',', ' ') }} FCFA</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted" style="padding: 30px;">
                                        <i class="fa fa-info-circle fa-2x"></i><br><br>
                                        Aucune donnée disponible pour les critères sélectionnés
                                    </td>
                                </tr>
                                @endforelse
                                @if($byOperator->count() > 0)
                                <tr class="bg-light-gray">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-center"><strong>{{ $byOperator->sum('count') }}</strong></td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <strong class="text-green">{{ number_format($byOperator->sum('total_balance'), 0, ',', ' ') }} FCFA</strong>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Par Location (si plusieurs locations) -->
    @if(isset($byLocation) && $byLocation && count($byLocation) > 1)
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
                                    <th class="text-center">Nombre de comptes</th>
                                    <th class="text-right">Solde total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byLocation as $item)
                                <tr>
                                    <td>
                                        <i class="fa fa-building text-blue"></i>
                                        <strong>{{ $item->location_name ?? 'N/A' }}</strong>
                                    </td>
                                    <td class="text-center"><strong>{{ $item->count }}</strong></td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <strong class="text-green">{{ number_format($item->total_balance, 0, ',', ' ') }} FCFA</strong>
                                    </td>
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
</section>
@endsection