@extends('layouts.app')

@section('title', 'Gestion des Commissions')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Gestion des Commissions</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Liste des commissions</h3>
            <div class="box-tools">
                <button class="btn btn-primary" data-toggle="modal" data-target="#commissionModal">
                    <i class="fa fa-plus"></i> Nouvelle Commission
                </button>
            </div>
        </div>
        
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Opérateur</th>
                            <th>Type Transaction</th>
                            <th>Type Commission</th>
                            <th>Valeur</th>
                            <th>Plage Montant</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $commission)
                        <tr>
                            <td>
                                @php
                                $op = config('multiservices.operators')[$commission->operator] ?? [];
                                @endphp
                                <span class="label" style="background-color: {{ $op['color'] ?? '#999' }}">
                                    {{ $op['name'] ?? $commission->operator }}
                                </span>
                            </td>
                            <td>{{ config('multiservices.transaction_types')[$commission->transaction_type] ?? $commission->transaction_type }}</td>
                            <td>
                                @if($commission->commission_type === 'fixed')
                                    Fixe
                                @else
                                    Pourcentage
                                @endif
                            </td>
                            <td>
                                @if($commission->commission_type === 'fixed')
                                    {{ number_format($commission->commission_value, 0, ',', ' ') }} FCFA
                                @else
                                    {{ $commission->commission_value }}%
                                @endif
                            </td>
                            <td>
                                @if($commission->min_amount || $commission->max_amount)
                                    {{ number_format($commission->min_amount ?? 0, 0, ',', ' ') }} - 
                                    {{ $commission->max_amount ? number_format($commission->max_amount, 0, ',', ' ') : '∞' }} FCFA
                                @else
                                    Tous montants
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-toggle-commission {{ $commission->is_active ? 'btn-success' : 'btn-default' }}" 
                                        data-id="{{ $commission->id }}">
                                    {{ $commission->is_active ? 'Actif' : 'Inactif' }}
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-primary btn-edit-commission" 
                                        data-id="{{ $commission->id }}"
                                        data-operator="{{ $commission->operator }}"
                                        data-type="{{ $commission->transaction_type }}"
                                        data-commission-type="{{ $commission->commission_type }}"
                                        data-value="{{ $commission->commission_value }}"
                                        data-min="{{ $commission->min_amount }}"
                                        data-max="{{ $commission->max_amount }}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-xs btn-danger btn-delete-commission" data-id="{{ $commission->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune commission configurée</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="commissionForm">
                @csrf
                <input type="hidden" id="commission_id" name="commission_id">
                
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Commission</h4>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label>Opérateur *</label>
                        <select name="operator" id="modal_operator" class="form-control" required>
                            @foreach($operators as $key => $op)
                            <option value="{{ $key }}">{{ $op['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type Transaction *</label>
                        <select name="transaction_type" id="modal_transaction_type" class="form-control" required>
                            <option value="all">Tous</option>
                            @foreach($transactionTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type Commission *</label>
                        <select name="commission_type" id="modal_commission_type" class="form-control" required>
                            <option value="fixed">Fixe</option>
                            <option value="percentage">Pourcentage</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Valeur *</label>
                        <input type="number" name="commission_value" id="modal_commission_value" class="form-control" step="0.01" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Montant Min</label>
                                <input type="number" name="min_amount" id="modal_min_amount" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Montant Max</label>
                                <input type="number" name="max_amount" id="modal_max_amount" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Submit form
    $('#commissionForm').submit(function(e) {
        e.preventDefault();
        
        var id = $('#commission_id').val();
        var url = id ? '/multiservices/commissions/' + id : '{{ route("multiservices.commissions.store") }}';
        var method = id ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    location.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    // Edit
    $('.btn-edit-commission').click(function() {
        var data = $(this).data();
        $('#commission_id').val(data.id);
        $('#modal_operator').val(data.operator);
        $('#modal_transaction_type').val(data.type);
        $('#modal_commission_type').val(data.commissionType);
        $('#modal_commission_value').val(data.value);
        $('#modal_min_amount').val(data.min);
        $('#modal_max_amount').val(data.max);
        $('#commissionModal').modal('show');
    });

    // Toggle active
    $('.btn-toggle-commission').click(function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/multiservices/commissions/' + id + '/toggle',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    location.reload();
                }
            }
        });
    });

    // Delete
    $('.btn-delete-commission').click(function() {
        var id = $(this).data('id');
        swal({
            title: 'Confirmer',
            text: 'Supprimer cette commission ?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '/multiservices/commissions/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            location.reload();
                        }
                    }
                });
            }
        });
    });

    // Reset form on modal hide
    $('#commissionModal').on('hidden.bs.modal', function() {
        $('#commissionForm')[0].reset();
        $('#commission_id').val('');
    });
});
</script>
@endsection
