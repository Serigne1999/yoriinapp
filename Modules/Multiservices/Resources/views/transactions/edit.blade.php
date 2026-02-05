@extends('layouts.app')

@section('title', 'Modifier Transaction')

@section('content')
<section class="content-header">
    <h1>Modifier la Transaction
        <small>{{ $transaction->reference_number }}</small>
    </h1>
</section>

<section class="content">
    <form action="{{ route('multiservices.update', $transaction->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informations de la Transaction</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Opérateur *</label>
                                    <select name="operator" class="form-control" required>
                                        <option value="">Sélectionner</option>
                                        @foreach($operators as $key => $op)
                                        <option value="{{ $key }}" {{ $transaction->operator == $key ? 'selected' : '' }}>
                                            {{ $op['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de transaction *</label>
                                    <select name="transaction_type" class="form-control" required>
                                        <option value="">Sélectionner</option>
                                        @foreach($transactionTypes as $key => $label)
                                        <option value="{{ $key }}" {{ $transaction->transaction_type == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Montant *</label>
                                    <input type="number" name="amount" class="form-control" value="{{ $transaction->amount }}" required min="0" step="0.01">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Frais</label>
                                    <input type="number" name="fee" class="form-control" value="{{ $transaction->fee }}" min="0" step="0.01" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Total</label>
                                    <input type="number" name="total" class="form-control" value="{{ $transaction->total }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Expéditeur</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nom complet *</label>
                                    <input type="text" name="sender_name" class="form-control" value="{{ $transaction->sender_name }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Téléphone *</label>
                                    <input type="text" name="sender_phone" class="form-control" value="{{ $transaction->sender_phone }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>N° Pièce d'identité</label>
                                    <input type="text" name="sender_id_number" class="form-control" value="{{ $transaction->sender_id_number }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Destinataire</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nom complet *</label>
                                    <input type="text" name="receiver_name" class="form-control" value="{{ $transaction->receiver_name }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Téléphone *</label>
                                    <input type="text" name="receiver_phone" class="form-control" value="{{ $transaction->receiver_phone }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>N° Pièce d'identité</label>
                                    <input type="text" name="receiver_id_number" class="form-control" value="{{ $transaction->receiver_id_number }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $transaction->notes }}</textarea>
                        </div>
                    </div>
                    
                    <div class="box-footer">
                        <a href="{{ route('multiservices.index') }}" class="btn btn-default">Annuler</a>
                        <button type="submit" class="btn btn-primary pull-right">
                            <i class="fa fa-save"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection
