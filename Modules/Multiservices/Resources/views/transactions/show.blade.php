@extends('layouts.app')

@section('title', 'Détails Transaction')

@section('content')
<section class="content-header">
    <h1>Détails de la Transaction
        <small>{{ $transaction->reference_number }}</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Informations Générales</h3>
                    <div class="box-tools">
                        <a href="{{ route('multiservices.index') }}" class="btn btn-sm btn-default">
                            <i class="fa fa-arrow-left"></i> Retour
                        </a>
                        @can('multiservices.update')
                        @if($transaction->canBeModified())
                        <a href="{{ route('multiservices.edit', $transaction->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-edit"></i> Modifier
                        </a>
                        @endif
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th width="40%">Référence</th>
                                    <td><strong>{{ $transaction->reference_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Opérateur</th>
                                    <td>{{ strtoupper(str_replace('_', ' ', $transaction->operator)) }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $transaction->transactionType ? $transaction->transactionType->name : 'Type inconnu' }}</td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        @if($transaction->status == 'completed')
                                            <span class="label label-success">Complétée</span>
                                        @elseif($transaction->status == 'pending')
                                            <span class="label label-warning">En attente</span>
                                        @elseif($transaction->status == 'canceled')
                                            <span class="label label-default">Annulée</span>
                                        @else
                                            <span class="label label-danger">Échouée</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Agent</th>
                                    <td>{{ $transaction->user->first_name ?? 'N/A' }} {{ $transaction->user->last_name ?? '' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th width="40%">Montant</th>
                                    <td><strong>{{ number_format($transaction->amount, 0) }} FCFA</strong></td>
                                </tr>
                                <tr>
                                    <th>Frais</th>
                                    <td>{{ number_format($transaction->fee, 0) }} FCFA</td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td><strong class="text-green">{{ number_format($transaction->total, 0) }} FCFA</strong></td>
                                </tr>
                                <tr>
                                    <th>Profit</th>
                                    <td><strong class="text-blue">{{ number_format($transaction->profit, 0) }} FCFA</strong></td>
                                </tr>
                                <tr>
                                    <th>Méthode de paiement</th>
                                    <td>{{ $transaction->payment_method ?? 'Espèces' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations Expéditeur/Destinataire -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Détails des Parties</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Expéditeur</h4>
                            <table class="table">
                                <tr>
                                    <th width="40%">Nom</th>
                                    <td>{{ $transaction->sender_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td>{{ $transaction->sender_phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Pièce d'identité</th>
                                    <td>{{ $transaction->sender_id_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Destinataire</h4>
                            <table class="table">
                                <tr>
                                    <th width="40%">Nom</th>
                                    <td>{{ $transaction->receiver_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td>{{ $transaction->receiver_phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Pièce d'identité</th>
                                    <td>{{ $transaction->receiver_id_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($transaction->notes)
            <div class="box box-warning">
                <div class="box-header">
                    <h3 class="box-title">Notes</h3>
                </div>
                <div class="box-body">
                    {{ $transaction->notes }}
                </div>
            </div>
            @endif

            @if($transaction->status == 'canceled' && $transaction->cancel_reason)
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Motif d'annulation</h3>
                </div>
                <div class="box-body">
                    {{ $transaction->cancel_reason }}
                    <br><small class="text-muted">Annulée par {{ $transaction->canceledBy->first_name ?? 'N/A' }} le {{ $transaction->canceled_at ? $transaction->canceled_at->format('d/m/Y H:i') : 'N/A' }}</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
