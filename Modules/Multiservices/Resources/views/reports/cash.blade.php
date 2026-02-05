@extends('layouts.app')

@section('title', 'Rapport de Caisse - Multiservices')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Rapport de Caisse - Multiservices
        <small>{{ $cashRegister ? 'Caisse #' . $cashRegister->id : 'Aucune caisse active' }}</small>
    </h1>
</section>

<section class="content">
    <!-- Filtre Location et Période -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
                    <form method="GET" action="{{ route('multiservices.reports.cash') }}" class="form-inline">
                        @if(count($locations) > 1)
                        <div class="form-group" style="margin-right: 20px;">
                            <label style="margin-right: 10px; font-weight: 600;">Lieu d'affaires:</label>
                            <select name="location_id" class="form-control" style="min-width: 200px;">
                                @foreach($locations as $id => $name)
                                <option value="{{ $id }}" {{ $locationId == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="form-group" style="margin-right: 20px;">
                            <label style="font-weight: 600;"><i class="fa fa-map-marker"></i> Location:</label>
                            <span style="margin-left: 10px;">{{ $locations[$locationId] ?? 'N/A' }}</span>
                        </div>
                        @endif
                        
                        <div class="form-group" style="margin-right: 20px;">
                            <label style="margin-right: 10px; font-weight: 600;">Période:</label>
                            <select name="period" class="form-control" id="period_filter" style="min-width: 180px;">
                                <option value="today" {{ ($period ?? 'today') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                                <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Hier</option>
                                <option value="last_7" {{ ($period ?? '') == 'last_7' ? 'selected' : '' }}>Les 7 derniers jours</option>
                                <option value="last_30" {{ ($period ?? '') == 'last_30' ? 'selected' : '' }}>Les 30 derniers jours</option>
                                <option value="this_month" {{ ($period ?? '') == 'this_month' ? 'selected' : '' }}>Ce mois-ci</option>
                                <option value="last_month" {{ ($period ?? '') == 'last_month' ? 'selected' : '' }}>Le mois dernier</option>
                                <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Plage personnalisée</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="custom_dates" style="margin-right: 10px; {{ ($period ?? '') == 'custom' ? '' : 'display: none;' }}">
                            <label style="margin-right: 5px;">Du:</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? date('Y-m-d') }}" style="width: 150px;">
                        </div>
                        
                        <div class="form-group" id="custom_dates_end" style="margin-right: 20px; {{ ($period ?? '') == 'custom' ? '' : 'display: none;' }}">
                            <label style="margin-right: 5px;">Au:</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? date('Y-m-d') }}" style="width: 150px;">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Filtrer
                        </button>
                        
                        <a href="{{ route('cash-register.index') }}" class="btn btn-default pull-right">
                            <i class="fa fa-cash-register"></i> Gérer les caisses
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($cashRegister)
    <!-- Stats Multiservices -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-gray">
                <span class="info-box-icon"><i class="fa fa-cash-register"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">OUVERTURE CAISSE</span>
                    <span class="info-box-number">{{ number_format($openingAmount, 0, ',', ' ') }} FCFA</span>
                    <span class="progress-description">{{ $cashRegister->opened_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">DÉPÔTS</span>
                    <span class="info-box-number">{{ number_format($deposits, 0, ',', ' ') }} FCFA</span>
                    <span class="progress-description">{{ $depositsCount }} transaction(s)</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">RETRAITS</span>
                    <span class="info-box-number">{{ number_format($withdrawals, 0, ',', ' ') }} FCFA</span>
                    <span class="progress-description">{{ $withdrawalsCount }} transaction(s)</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">SOLDE THÉORIQUE</span>
                    <span class="info-box-number">{{ number_format($theoreticalBalance, 0, ',', ' ') }} FCFA</span>
                    <span class="progress-description">Solde actuel</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des mouvements -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Détails des Mouvements Multiservices</h3>
                    <div class="box-tools">
                        <span class="label {{ $cashRegister->status === 'open' ? 'label-success' : 'label-default' }}">
                            {{ $cashRegister->status === 'open' ? 'Caisse Ouverte' : 'Caisse Fermée' }}
                        </span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Heure</th>
                                    <th>Type</th>
                                    <th class="text-right">Montant</th>
                                    <th>Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('d/m H:i:s') }}</td>
                                    <td>
                                        @if($movement->type === 'deposit')
                                        <span class="label label-success">
                                            <i class="fa fa-arrow-down"></i> DÉPÔT
                                        </span>
                                        @elseif($movement->type === 'withdrawal')
                                        <span class="label label-danger">
                                            <i class="fa fa-arrow-up"></i> RETRAIT
                                        </span>
                                        @elseif($movement->type === 'funding')
                                        <span class="label label-primary">
                                            <i class="fa fa-money"></i> ALIMENTATION
                                        </span>
                                        @elseif($movement->type === 'funding_cancel')
                                        <span class="label label-danger">
                                            <i class="fa fa-undo"></i> ANNULATION ALIM
                                        </span>
                                        
                                        @elseif($movement->type === 'expense_cancel')
                                        <span class="label label-danger">
                                            <i class="fa fa-undo"></i> ANNULATION SORTIE
                                        </span>
                                         @elseif($movement->type === 'expense')
                                        <span class="label label-warning">
                                            <i class="fa fa-minus-circle"></i> SORTIE
                                        </span>
                                        @elseif($movement->type === 'opening')
                                        <span class="label label-info">
                                            <i class="fa fa-unlock"></i> OUVERTURE
                                        </span>
                                        @elseif($movement->type === 'closing')
                                        <span class="label label-default">
                                            <i class="fa fa-lock"></i> FERMETURE
                                        </span>
                                        @else
                                        <span class="label label-default">{{ strtoupper($movement->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if(in_array($movement->type, ['deposit', 'funding', 'opening']))
                                        <span class="text-green" style="font-weight: bold; font-size: 16px;">
                                            +{{ number_format($movement->amount, 0, ',', ' ') }} FCFA
                                        </span>
                                        @else
                                        <span class="text-red" style="font-weight: bold; font-size: 16px;">
                                            -{{ number_format($movement->amount, 0, ',', ' ') }} FCFA
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($movement->multiservice_transaction_id)
                                        <small class="text-muted">Ref: #{{ $movement->multiservice_transaction_id }}</small><br>
                                        @endif
                                        <small>{{ $movement->notes }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fa fa-info-circle"></i> Aucun mouvement pour cette période
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-light-blue">
                                    <td colspan="2"><strong>TOTAL IMPACT SUR CAISSE</strong></td>
                                    <td class="text-right">
                                        <strong style="font-size: 18px;">
                                            {{ $netImpact >= 0 ? '+' : '' }}{{ number_format($netImpact, 0, ',', ' ') }} FCFA
                                        </strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info caisse -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Informations Caisse</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <strong>Caisse #{{ $cashRegister->id }}</strong>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <strong>Location :</strong> {{ $cashRegister->location->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <strong>Ouverture :</strong> {{ $cashRegister->opened_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-3 col-sm-6">
                            @if($cashRegister->status === 'closed' && $cashRegister->closed_at)
                            <strong>Fermeture :</strong> {{ \Carbon\Carbon::parse($cashRegister->closed_at)->format('d/m/Y H:i') }}
                            @else
                            <span class="label label-success"><i class="fa fa-unlock"></i> En cours</span>
                            @endif
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <a href="{{ route('cash-register.show', $cashRegister->id) }}" class="btn btn-info">
                            <i class="fa fa-eye"></i> Voir détails complets
                        </a>
                        
                        @if($cashRegister->status === 'open')
                        <a href="{{ route('cash-register.index') }}" class="btn btn-success">
                            <i class="fa fa-cash-register"></i> Gérer la caisse
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @else
    <!-- Aucune caisse -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body text-center" style="padding: 50px;">
                    <i class="fa fa-info-circle" style="font-size: 48px; color: #3c8dbc; margin-bottom: 20px;"></i>
                    <h3>Aucune caisse trouvée</h3>
                    <p>Aucune caisse n'a été ouverte pour cette période et location.</p>
                    
                    <a href="{{ route('cash-register.create') }}" class="btn btn-primary btn-lg">
                        <i class="fa fa-plus"></i> Ouvrir une caisse
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodFilter = document.getElementById('period_filter');
    const locationFilter = document.querySelector('select[name="location_id"]');
    const customDates = document.getElementById('custom_dates');
    const customDatesEnd = document.getElementById('custom_dates_end');
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    // Auto-submit sur changement de période
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDates.style.display = 'inline-block';
                customDatesEnd.style.display = 'inline-block';
            } else {
                customDates.style.display = 'none';
                customDatesEnd.style.display = 'none';
                this.form.submit();
            }
        });
    }
    
    // Auto-submit sur changement de location
    if (locationFilter) {
        locationFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    // Auto-submit sur changement des dates personnalisées
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            if (periodFilter.value === 'custom') {
                this.form.submit();
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            if (periodFilter.value === 'custom') {
                this.form.submit();
            }
        });
    }
});
</script>

<style>
.info-box-text {
    color: #FFF;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 20px !important;
}

.progress-description {
    color: #FFF;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endsection