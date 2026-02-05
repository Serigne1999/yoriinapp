@extends('layouts.app')

@section('title', 'Transactions Multiservices')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Transactions Multiservices</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Liste des transactions</h3>
            <div class="box-tools">
                @can('multiservices.create')
                <a href="{{ route('multiservices.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Nouvelle Transaction
                </a>
                @endcan
            </div>
        </div>

        <div class="box-body">
            <!-- Filtres -->
            <div class="row">
                <!-- Période -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Période</label>
                        <select class="form-control" id="filter_period">
                            <option value="today">Aujourd'hui</option>
                            <option value="yesterday">Hier</option>
                            <option value="last_7">Les 7 derniers jours</option>
                            <option value="last_30">Les 30 derniers jours</option>
                            <option value="this_month" selected>Ce mois-ci</option>
                            <option value="last_month">Le mois dernier</option>
                            <option value="this_year">Cette année</option>
                            <option value="custom">Plage personnalisée</option>
                        </select>
                    </div>
                </div>
                
                <!-- Dates personnalisées (cachées par défaut) -->
                <div class="col-md-2" id="custom_dates" style="display: none;">
                    <div class="form-group">
                        <label>Date début</label>
                        <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-01') }}">
                    </div>
                </div>
                <div class="col-md-2" id="custom_dates_end" style="display: none;">
                    <div class="form-group">
                        <label>Date fin</label>
                        <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                
                <!-- Opérateur -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Opérateur</label>
                        <select class="form-control" id="filter_operator">
                            <option value="">Tous</option>
                            @foreach($operators as $key => $op)
                            <option value="{{ $key }}">{{ $op }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Location</label>
                        <select name="location_filter" id="location_filter" class="form-control">
                            <option value="">Toutes</option>
                            @foreach($locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Type -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Type</label>
                        <select class="form-control" id="filter_type">
                            <option value="">Tous</option>
                            @foreach($transactionTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Statut -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Statut</label>
                        <select class="form-control" id="filter_status">
                            <option value="">Tous</option>
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Référence -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Référence</label>
                        <input type="text" class="form-control" id="filter_reference" placeholder="MS...">
                    </div>
                </div>
                
                <!-- Bouton réinitialiser -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-default btn-block" id="reset_filters">
                            <i class="fa fa-refresh"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="transactions_table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Opérateur</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Frais</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Agent</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-throttle-debounce/1.1/jquery.ba-throttle-debounce.min.js"></script>
<script>
$(document).ready(function() {
    // Fonction pour calculer les dates selon la période
    function getPeriodDates(period) {
        var today = new Date();
        var start, end;
        
        switch(period) {
            case 'today':
                start = end = today.toISOString().split('T')[0];
                break;
            case 'yesterday':
                var yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                start = end = yesterday.toISOString().split('T')[0];
                break;
            case 'last_7':
                end = today.toISOString().split('T')[0];
                var last7 = new Date(today);
                last7.setDate(last7.getDate() - 7);
                start = last7.toISOString().split('T')[0];
                break;
            case 'last_30':
                end = today.toISOString().split('T')[0];
                var last30 = new Date(today);
                last30.setDate(last30.getDate() - 30);
                start = last30.toISOString().split('T')[0];
                break;
            case 'this_month':
                start = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                end = today.toISOString().split('T')[0];
                break;
            case 'last_month':
                var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                start = lastMonth.toISOString().split('T')[0];
                var lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                end = lastMonthEnd.toISOString().split('T')[0];
                break;
            case 'this_year':
                start = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                end = today.toISOString().split('T')[0];
                break;
            case 'custom':
                start = $('#start_date').val();
                end = $('#end_date').val();
                break;
            default:
                start = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                end = today.toISOString().split('T')[0];
        }
        
        return { start: start, end: end };
    }
    
    var table = $('#transactions_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ action([\Modules\Multiservices\Http\Controllers\MultiservicesController::class, 'index']) }}",
            data: function(d) {
                var period = $('#filter_period').val();
                var dates = getPeriodDates(period);
                
                d.operator = $('#filter_operator').val();
                d.location_id = $('#location_filter').val();
                d.type = $('#filter_type').val();
                d.status = $('#filter_status').val();
                d.reference = $('#filter_reference').val();
                d.start_date = dates.start;
                d.end_date = dates.end;
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'reference_number', name: 'reference_number' },
            { data: 'created_at', name: 'created_at' },
            { data: 'operator', name: 'operator' },
            { data: 'location', name: 'location' },
            { data: 'type', name: 'type' },
            { data: 'amount', name: 'amount' },
            { data: 'fee', name: 'fee' },
            { data: 'total', name: 'total' },
            { data: 'status', name: 'status' },
            { data: 'user.first_name', name: 'user.first_name' }
        ],
        order: [[2, 'desc']]
    });

    // Gérer l'affichage des dates personnalisées
    $('#filter_period').change(function() {
        if ($(this).val() === 'custom') {
            $('#custom_dates, #custom_dates_end').show();
        } else {
            $('#custom_dates, #custom_dates_end').hide();
            table.ajax.reload();
        }
    });

    // Filtres
    $('#filter_operator, #filter_type, #filter_status, #location_filter').change(function() {
        table.ajax.reload();
    });
    
    $('#filter_reference').keyup($.debounce(500, function() {
        table.ajax.reload();
    }));
    
    $('#start_date, #end_date').change(function() {
        if ($('#filter_period').val() === 'custom') {
            table.ajax.reload();
        }
    });
    
    // Réinitialiser les filtres
    $('#reset_filters').click(function() {
        $('#filter_period').val('this_month');
        $('#filter_operator').val('');
        $('#location_filter').val('');
        $('#filter_type').val('');
        $('#filter_status').val('');
        $('#filter_reference').val('');
        $('#custom_dates, #custom_dates_end').hide();
        table.ajax.reload();
    });

    // Complete transaction
    $(document).on('click', '.complete-transaction', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        swal({
            title: 'Confirmer',
            text: 'Compléter cette transaction ?',
            icon: 'warning',
            buttons: true,
        }).then((willComplete) => {
            if (willComplete) {
                $.ajax({
                    url: '/multiservices/' + id + '/complete',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    // Cancel transaction
    $(document).on('click', '.cancel-transaction', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        swal({
            title: 'Motif d\'annulation',
            content: "input",
            buttons: true,
        }).then((reason) => {
            if (reason) {
                $.ajax({
                    url: '/multiservices/' + id + '/cancel',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: reason
                    },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    // Delete transaction
    $(document).on('click', '.delete-transaction', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        swal({
            title: 'Confirmer',
            text: 'Supprimer cette transaction ?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '/multiservices/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection