@extends('layouts.app')

@section('title', 'Comptes Opérateurs')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Comptes Opérateurs</h1>
</section>

<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_accounts" data-toggle="tab"><i class="fa fa-list"></i> Mes Comptes</a></li>
            <li><a href="#tab_reports" data-toggle="tab"><i class="fa fa-chart-bar"></i> Rapports</a></li>
        </ul>

        <div class="tab-content">
            <!-- Onglet 1 : Liste des comptes -->
            <div class="tab-pane active" id="tab_accounts">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Liste des comptes</h3>
                        <div class="box-tools">
                            @can('multiservices.create')
                            <button class="btn btn-primary" data-toggle="modal" data-target="#add_account_modal">
                                <i class="fa fa-plus"></i> Ajouter un compte
                            </button>
                            @endcan
                        </div>
                    </div>
                    <div class="box-body">
                        <div>
                            <div class="table-responsive">    
                                <table class="table table-bordered table-striped" id="accounts_table">
                                    <thead>
                                        <tr>
                                            <th>Opérateur</th>
                                            <th>Location</th>
                                            <th>Nom du compte</th>
                                            <th>N° de compte</th>
                                            <th>Solde</th>
                                            <th>Statut</th>
                                            <th>Dernière MAJ</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet 2 : Rapports -->
            <div class="tab-pane" id="tab_reports">
                <div class="row">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('multiservices.accounts.index') }}" class="form-inline" style="margin-bottom: 20px;">
                            <input type="hidden" name="tab" value="reports">
                            <div class="form-group">
                                <label>Période:</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-01')) }}">
                            </div>
                            <span style="margin: 0 10px;">→</span>
                            <div class="form-group">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Filtrer
                            </button>
                        </form>

                        <div id="reports_content">
                            <p class="text-muted text-center" style="padding: 40px;">
                                <i class="fa fa-chart-line fa-3x"></i><br><br>
                                Sélectionnez une période et cliquez sur "Filtrer" pour voir les rapports
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal : Ajouter un compte -->
<div class="modal fade" id="add_account_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add_account_form">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Ajouter un compte opérateur</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Opérateur *</label>
                        <select name="operator" class="form-control" required>
                            <option value="">Sélectionner</option>
                            @foreach($operators as $key => $op)
                            <option value="{{ $key }}">{{ $op['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">  <!-- ← AJOUTER -->
                        <label>Location *</label>
                        <select name="location_id" class="form-control" required>
                            <option value="">Sélectionner</option>
                            @foreach($locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nom du compte *</label>
                        <input type="text" name="account_name" class="form-control" placeholder="Ex: Compte Wave Principal" required>
                    </div>

                    <div class="form-group">
                        <label>Numéro de compte *</label>
                        <input type="text" name="account_number" class="form-control" placeholder="Ex: +221 77 123 45 67" required>
                    </div>

                    <div class="form-group">
                        <label>Solde initial *</label>
                        <input type="number" name="initial_balance" class="form-control" value="0" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal : Alimenter un compte -->
<div class="modal fade" id="fund_account_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fund_account_form">
                @csrf
                <input type="hidden" name="account_id" id="fund_account_id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Alimenter le compte</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Type d'opération *</label>
                        <select name="type" class="form-control" required>
                            <option value="deposit">Dépôt (+)</option>
                            <option value="withdrawal">Retrait (-)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Montant *</label>
                        <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Motif</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Raison de l'opération"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Valider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // DataTable
    var table = $('#accounts_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("multiservices.accounts.index") }}',
            data: { table: 'accounts' }
        },
        columns: [
            { data: 'operator', name: 'operator' },
            { data: 'location', name: 'location' },
            { data: 'account_name', name: 'account_name' },
            { data: 'account_number', name: 'account_number' },
            { data: 'balance', name: 'balance' },
            { data: 'is_active', name: 'is_active' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Ajouter un compte
    $('#add_account_form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("multiservices.accounts.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(result) {
                if (result.success) {
                    $('#add_account_modal').modal('hide');
                    toastr.success(result.msg);
                    table.ajax.reload();
                    $('#add_account_form')[0].reset();
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                toastr.error('Une erreur est survenue');
            }
        });
    });

    // Ouvrir modal Alimenter
    $(document).on('click', '.fund-account', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#fund_account_id').val(id);
        $('#fund_account_modal').modal('show');
    });

    // Alimenter un compte
    $('#fund_account_form').submit(function(e) {
        e.preventDefault();
        var id = $('#fund_account_id').val();
        
        $.ajax({
            url: '/multiservices/accounts/' + id + '/fund',
            method: 'POST',
            data: $(this).serialize(),
            success: function(result) {
                if (result.success) {
                    $('#fund_account_modal').modal('hide');
                    toastr.success(result.msg);
                    table.ajax.reload();
                    $('#fund_account_form')[0].reset();
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                toastr.error('Une erreur est survenue');
            }
        });
    });

    // Supprimer un compte
    $(document).on('click', '.delete-account', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        swal({
            title: 'Confirmer',
            text: 'Supprimer ce compte ?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '/multiservices/accounts/' + id,
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
