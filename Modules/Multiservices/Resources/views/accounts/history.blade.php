@extends('layouts.app')

@section('title', 'Historique du compte')

@section('content')
@include('multiservices::layouts.nav')
<section class="content-header">
    <h1>Historique du compte
        <small>{{ $account->operator_name }} - {{ $account->location ? $account->location->name : '' }}</small>
    </h1>
</section>

<section class="content">
    <!-- Info box du compte -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">N° Compte</span>
                    <span class="info-box-number" style="color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ $account->account_number }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Solde Actuel</span>
                    <span class="info-box-number" style="color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ number_format($account->balance, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-history"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Mouvements</span>
                    <span class="info-box-number" style="color: #fff;">{{ $transactions->total() }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-{{ $account->is_active ? 'green' : 'red' }}">
                <span class="info-box-icon"><i class="fa fa-{{ $account->is_active ? 'check' : 'times' }}-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="color: #fff;">Statut</span>
                    <span class="info-box-number" style="color: #fff;">{{ $account->is_active ? 'Actif' : 'Inactif' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid collapsed-box">
                <div class="box-header with-border" style="cursor: pointer;" data-widget="collapse">
                    <h3 class="box-title" style="color: #3c8dbc;">
                        <i class="fa fa-filter"></i> Filtres
                    </h3>
                </div>
                <div class="box-body">
                    <form method="GET" action="{{ route('multiservices.accounts.history', $account->id) }}" class="form-inline">
                        <div class="form-group">
                            <label>Type :</label>
                            <select name="type" class="form-control">
                                <option value="">Tous</option>
                                <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Dépôts</option>
                                <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Retraits</option>
                                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Ajustements</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-left: 10px;">
                            <label>Période :</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" class="form-control" id="history_date_filter" readonly>
                            </div>
                            <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-left: 10px;">
                            <i class="fa fa-filter"></i> Filtrer
                        </button>

                        <a href="{{ route('multiservices.accounts.history', $account->id) }}" class="btn btn-default">
                            <i class="fa fa-refresh"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau historique -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Mouvements du compte</h3>
                    <div class="box-tools">
                        <a href="{{ route('multiservices.accounts.index') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="bg-gray">
                                <tr>
                                    <th><i class="fa fa-calendar"></i> Date</th>
                                    <th><i class="fa fa-tag"></i> Type</th>
                                    <th class="text-right"><i class="fa fa-money"></i> Montant</th>
                                    <th class="text-right">Solde avant</th>
                                    <th class="text-right">Solde après</th>
                                    <th><i class="fa fa-comment"></i> Motif</th>
                                    <th><i class="fa fa-user"></i> Par</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td style="white-space: nowrap;">
                                        <i class="fa fa-clock-o text-muted"></i>
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="white-space: nowrap;">
                                        @if($transaction->type == 'deposit')
                                            <span class="label label-success"><i class="fa fa-arrow-up"></i> Dépôt</span>
                                        @elseif($transaction->type == 'withdrawal')
                                            <span class="label label-warning"><i class="fa fa-arrow-down"></i> Retrait</span>
                                        @else
                                            <span class="label label-info"><i class="fa fa-exchange"></i> Ajustement</span>
                                        @endif
                                    </td>
                                    <td class="text-right" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                        @if($transaction->type == 'deposit')
                                            <strong class="text-green">+{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA</strong>
                                        @else
                                            <strong class="text-red">-{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA</strong>
                                        @endif
                                    </td>
                                    <td class="text-right" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                        {{ number_format($transaction->balance_before, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-right" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                        <strong>{{ number_format($transaction->balance_after, 0, ',', ' ') }} FCFA</strong>
                                    </td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <small>{{ $transaction->notes ?? '-' }}</small>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <i class="fa fa-user-circle text-blue"></i>
                                        {{ $transaction->creator->first_name ?? 'N/A' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted" style="padding: 30px;">
                                        <i class="fa fa-info-circle fa-2x"></i><br><br>
                                        Aucun mouvement enregistré pour les critères sélectionnés
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($transactions->hasPages())
                    <div class="text-center" style="margin-top: 20px;">
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('javascript')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
$(document).ready(function() {
    console.log('🔧 Initialisation daterangepicker historique...');
    
    // Clic sur le header entier pour ouvrir/fermer les filtres
    $('.box-header[data-widget="collapse"]').on('click', function(e) {
        e.preventDefault();
        var box = $(this).closest('.box');
        var body = box.find('.box-body');
        
        if (box.hasClass('collapsed-box')) {
            // Ouvrir
            box.removeClass('collapsed-box');
            body.slideDown(300);
        } else {
            // Fermer
            box.addClass('collapsed-box');
            body.slideUp(300);
        }
    });

    // Configuration du daterangepicker (comme UltimatePOS)
    var start = moment().subtract(29, 'days');
    var end = moment();

    @if(request('start_date') && request('end_date'))
        start = moment('{{ request('start_date') }}');
        end = moment('{{ request('end_date') }}');
    @endif

    function cb(start, end) {
        $('#history_date_filter').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        $('#start_date').val(start.format('YYYY-MM-DD'));
        $('#end_date').val(end.format('YYYY-MM-DD'));
    }

    $('#history_date_filter').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Appliquer',
            cancelLabel: 'Annuler',
            fromLabel: 'De',
            toLabel: 'À',
            customRangeLabel: 'Personnalisé',
            weekLabel: 'S',
            daysOfWeek: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            firstDay: 1
        },
        ranges: {
            "Aujourd'hui": [moment(), moment()],
            'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Les 7 derniers jours': [moment().subtract(6, 'days'), moment()],
            'Les 30 derniers jours': [moment().subtract(29, 'days'), moment()],
            'Ce mois-ci': [moment().startOf('month'), moment().endOf('month')],
            'Le mois dernier': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            "Ce mois l'année dernière": [moment().subtract(1, 'year').startOf('month'), moment().subtract(1, 'year').endOf('month')],
            'Cette année': [moment().startOf('year'), moment().endOf('year')],
            "L'année dernière": [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            'Année financière en cours': [moment().month(0).date(1), moment().month(11).date(31)],
            'Dernier exercice': [moment().subtract(1, 'year').month(0).date(1), moment().subtract(1, 'year').month(11).date(31)]
        }
    }, cb);

    cb(start, end);
    console.log('✅ Daterangepicker initialisé');
});
</script>
@endpush