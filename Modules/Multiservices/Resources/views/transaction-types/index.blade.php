@extends('layouts.app')

@section('title', 'Types de Transactions')

@section('content')
@include('multiservices::layouts.nav')

<!-- Content Header -->
<section class="content-header">
    <h1>
        Types de Transactions
        <small>Gérez les types de transactions disponibles</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    
    @component('components.widget', ['class' => 'box-primary'])
        
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-list"></i> Liste des Types
            </h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTypeModal">
                    <i class="fa fa-plus"></i> Nouveau Type
                </button>
            </div>
        </div>

        <div class="box-body">
            @if(session('status'))
                <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }}">
                    {{ session('status.msg') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="types-table">
                    <thead>
                        <tr class="bg-light-blue">
                            <th width="5%">#</th>
                            <th width="30%">Nom</th>
                            <th width="45%">Description</th>
                            <th width="10%">Statut</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                        <tr data-type-id="{{ $type->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $type->name }}</strong></td>
                            <td>{{ $type->description ?? '-' }}</td>
                            <td class="text-center">
                                <span class="status-badge">
                                    {!! $type->getStatusBadge() !!}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        Actions <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                        <li>
                                            <a href="#" class="edit-type" data-id="{{ $type->id }}">
                                                <i class="fa fa-edit"></i> Modifier
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="toggle-type" data-id="{{ $type->id }}">
                                                <i class="fa fa-{{ $type->is_active ? 'ban' : 'check' }}"></i> 
                                                {{ $type->is_active ? 'Désactiver' : 'Activer' }}
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                        <li>
                                            <a href="#" class="delete-type text-red" data-id="{{ $type->id }}">
                                                <i class="fa fa-trash"></i> Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fa fa-info-circle"></i> Aucun type de transaction configuré
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endcomponent

</section>

<!-- Modal: Ajouter Type -->
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('multiservices.transaction-types.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus"></i> Nouveau Type de Transaction</h4>
                </div>
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label>Nom du Type <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required 
                               placeholder="Ex: Paiement Facture, Recharge Mobile, Crédit...">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Description optionnelle du type de transaction"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Modifier Type -->
<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-edit"></i> Modifier Type de Transaction</h4>
                </div>
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label>Nom du Type <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-save"></i> Modifier
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
    
    // CSS pour uniformiser les icônes des boutons
    $('<style>')
        .text('.modal-footer .btn i { font-size: 14px; margin-right: 5px; vertical-align: middle; }')
        .appendTo('head');
    
    // DataTable - DÉSACTIVÉ TEMPORAIREMENT POUR DEBUG
    /*
    $('#types-table').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
        }
    });
    */

    // Modifier Type
    $('.edit-type').click(function(e) {
        e.preventDefault();
        const typeId = $(this).data('id');
        
        $.get('/multiservices/transaction-types/' + typeId, function(data) {
            $('#edit-name').val(data.name);
            $('#edit-description').val(data.description);
            
            $('#editTypeForm').attr('action', '/multiservices/transaction-types/' + typeId);
            $('#editTypeModal').modal('show');
        });
    });

    // Toggle Status
    $('.toggle-type').click(function(e) {
        e.preventDefault();
        const typeId = $(this).data('id');
        const $link = $(this);
        const $row = $link.closest('tr');
        
        $.post('/multiservices/transaction-types/' + typeId + '/toggle', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                // Recharger la page pour mettre à jour le dropdown
                location.reload();
            }
        });
    });

    // Supprimer Type
    $('.delete-type').click(function(e) {
        e.preventDefault();
        const typeId = $(this).data('id');
        const $row = $(this).closest('tr');
        
        swal({
            title: LANG.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '/multiservices/transaction-types/' + typeId,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            $row.fadeOut(function() { $(this).remove(); });
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Erreur lors de la suppression';
                        toastr.error(message);
                    }
                });
            }
        });
    });

});
</script>
@endsection