@extends('layouts.app')
@section('title', 'Détail Caisse')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Caisse #{{ $register->id }} 
        @if($register->status === 'open')
            <span class="label label-success">Ouverte</span>
        @else
            <span class="label label-default">Fermée</span>
        @endif
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Mouvements de caisse</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date/Heure</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Solde après</th>
                                    <th>Notes</th>
                                    <th width="100">Actions</th> {{-- ⭐ NOUVELLE COLONNE --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($register->transactions as $tx)
                                <tr>
                                    <td>{{ $tx->created_at->format('d/m H:i') }}</td>
                                    <td>
                                        @if($tx->type === 'opening')
                                            <span class="label label-info">Ouverture</span>
                                            
                                        @elseif($tx->type === 'funding')
                                            <span class="label label-success">Alimentation</span>
                                            
                                        @elseif($tx->type === 'funding_cancel')
                                            <span class="label label-danger">
                                                <i class="fa fa-undo"></i> ANNULATION ALIM
                                            </span>
                                            
                                        @elseif($tx->type === 'expense')
                                            <span class="label label-warning">
                                                <i class="fa fa-minus-circle"></i> Sortie
                                            </span>
                                            
                                        @elseif($tx->type === 'expense_cancel')
                                            <span class="label label-danger">
                                                <i class="fa fa-undo"></i> ANNULATION SORTIE
                                            </span>
                                            
                                        @elseif($tx->type === 'deposit')
                                            <span class="label label-primary">Dépôt client</span>
                                            
                                        @elseif($tx->type === 'withdrawal')
                                            <span class="label label-warning">Retrait client</span>
                                            
                                        @elseif($tx->type === 'closing')
                                            <span class="label label-default">Fermeture</span>
                                            
                                        @else
                                            <span class="label label-default">{{ strtoupper($tx->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-bold">{{ number_format($tx->amount, 0) }} FCFA</td>
                                    <td class="text-blue">{{ number_format($tx->balance_after, 0) }} FCFA</td>
                                    <td><small>{{ $tx->notes }}</small></td>
                                    
                                    {{-- ⭐ NOUVELLE COLONNE ACTIONS --}}
                                    <td>
                                        @if($register->status === 'open')
                                            <div class="btn-group">
                                                <button class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                                                    Action <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    {{-- ALIMENTATION → Annuler --}}
                                                    @if($tx->type === 'funding' && !str_contains($tx->notes, '[ANNULÉ]'))
                                                        <li>
                                                            <a href="#" class="cancel-funding" data-id="{{ $tx->id }}">
                                                                <i class="fa fa-undo text-warning"></i> Annuler
                                                            </a>
                                                        </li>
                                                    @endif
                                                    
                                                    {{-- SORTIE → Annuler OU Supprimer --}}
                                                    @if($tx->type === 'expense' && !str_contains($tx->notes, '[ANNULÉ]'))
                                                        <li>
                                                            <a href="#" class="cancel-expense" data-id="{{ $tx->id }}">
                                                                <i class="fa fa-undo text-warning"></i> Annuler
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="#" class="delete-expense" data-id="{{ $tx->id }}">
                                                                <i class="fa fa-trash text-danger"></i> Supprimer
                                                            </a>
                                                        </li>
                                                    @endif
                                                    
                                                    {{-- SI AUCUNE ACTION --}}
                                                    @if(!in_array($tx->type, ['funding', 'expense']) || str_contains($tx->notes, '[ANNULÉ]'))
                                                        <li class="text-muted text-center">
                                                            <small>Aucune action disponible</small>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header">
                    <h3 class="box-title">Résumé</h3>
                </div>
                <div class="box-body">
                    <table class="table">
                        <tr>
                            <th>Ouverture :</th>
                            <td>{{ number_format($register->opening_amount, 0) }} FCFA</td>
                        </tr>
                        <tr class="text-bold text-primary">
                            <th>Montant attendu :</th>
                            <td>{{ number_format($register->expected_amount, 0) }} FCFA</td>
                        </tr>
                        @if($register->status === 'closed')
                        <tr>
                            <th>Comptage réel :</th>
                            <td>{{ number_format($register->closing_amount, 0) }} FCFA</td>
                        </tr>
                        @if($register->shortage > 0)
                        <tr class="text-danger">
                            <th>Manque :</th>
                            <td>{{ number_format($register->shortage, 0) }} FCFA</td>
                        </tr>
                        @endif
                        @if($register->excess > 0)
                        <tr class="text-success">
                            <th>Surplus :</th>
                            <td>{{ number_format($register->excess, 0) }} FCFA</td>
                        </tr>
                        @endif
                        @endif
                    </table>
                    
                    @if($register->status === 'open')
                    <button class="btn btn-success btn-block" data-toggle="modal" data-target="#fundModal">
                        <i class="fa fa-money"></i> Alimenter la caisse
                    </button>
                    
                    {{-- ⭐ NOUVEAU BOUTON PRÉLEVER --}}
                    <a href="{{ route('cash-register.expense', $register->id) }}" 
                       class="btn btn-warning btn-block" 
                       style="margin-top: 10px;">
                        <i class="fa fa-minus-circle"></i> Prélever
                    </a>
                    
                    <button class="btn btn-danger btn-block" data-toggle="modal" data-target="#closeModal" style="margin-top: 10px;">
                        <i class="fa fa-lock"></i> Fermer la caisse
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if($register->status === 'open')
<!-- Modal Alimentation -->
<div class="modal fade" id="fundModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Alimenter la caisse</h4>
            </div>
            <form action="{{ route('cash-register.fund', $register->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Montant *</label>
                        <input type="number" name="amount" class="form-control" required min="1" placeholder="Montant en FCFA">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Notes optionnelles..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Alimenter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Fermeture -->
<div class="modal fade" id="closeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-red">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Fermer la caisse</h4>
            </div>
            <form action="{{ route('cash-register.close', $register->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Montant théorique attendu : <strong>{{ number_format($register->expected_amount, 0) }} FCFA</strong>
                    </div>
                    <div class="form-group">
                        <label>Montant réel compté *</label>
                        <input type="number" name="closing_amount" class="form-control" required min="0" placeholder="Entrez le montant compté" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Notes de fermeture</label>
                        <textarea name="closing_notes" class="form-control" rows="3" placeholder="Notes optionnelles..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-lock"></i> Fermer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@section('javascript')
<script>
$(document).ready(function() {
    console.log('Script caisse chargé');
    
    // Annuler alimentation
    $(document).on('click', '.cancel-funding', function(e) {
        e.preventDefault();
        var transactionId = $(this).data('id');
        
        swal({
            title: 'Annuler cette alimentation ?',
            text: 'Le montant sera déduit de la caisse',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Non',
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: 'Oui, annuler'
                }
            },
            dangerMode: true,
        }).then((isConfirm) => {
            if (isConfirm) {
                $.ajax({
                    url: '/multiservices/caisse/{{ $register->id }}/cancel-funding/' + transactionId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: 'Annulation manuelle'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal('Annulé!', response.msg, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            swal('Erreur', response.msg, 'error');
                        }
                    },
                    error: function(xhr) {
                        swal('Erreur', 'Une erreur est survenue', 'error');
                        console.error(xhr);
                    }
                });
            }
        });
    });
    
    // Annuler prélèvement
    $(document).on('click', '.cancel-expense', function(e) {
        e.preventDefault();
        var transactionId = $(this).data('id');
        
        swal({
            title: 'Annuler ce prélèvement ?',
            text: 'Le montant sera recrédité à la caisse',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Non',
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: 'Oui, annuler'
                }
            },
            dangerMode: true,
        }).then((isConfirm) => {
            if (isConfirm) {
                $.ajax({
                    url: '/multiservices/caisse/{{ $register->id }}/cancel-expense/' + transactionId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reason: 'Annulation manuelle'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal('Annulé!', response.msg, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            swal('Erreur', response.msg, 'error');
                        }
                    },
                    error: function(xhr) {
                        swal('Erreur', 'Une erreur est survenue', 'error');
                        console.error(xhr);
                    }
                });
            }
        });
    });
    
    // Supprimer prélèvement
    $(document).on('click', '.delete-expense', function(e) {
        e.preventDefault();
        var transactionId = $(this).data('id');
        
        swal({
            title: 'Supprimer ce prélèvement ?',
            text: 'Cette action est irréversible !',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Non',
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: 'Oui, supprimer'
                }
            },
            dangerMode: true,
        }).then((isConfirm) => {
            if (isConfirm) {
                $.ajax({
                    url: '/multiservices/caisse/{{ $register->id }}/delete-expense/' + transactionId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal('Supprimé!', response.msg, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            swal('Erreur', response.msg, 'error');
                        }
                    },
                    error: function(xhr) {
                        swal('Erreur', 'Une erreur est survenue', 'error');
                        console.error(xhr);
                    }
                });
            }
        });
    });
});
</script>
@endsection
@endsection