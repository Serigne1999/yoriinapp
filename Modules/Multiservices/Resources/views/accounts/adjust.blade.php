@extends('layouts.app')

@section('title', 'Ajuster Solde Compte')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Ajuster Solde Compte
        <small>{{ $account->account_name }}</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Ajustement Comptable
                    </h3>
                </div>
                
                {!! Form::open(['route' => ['operator-accounts.adjust.process', $account->id], 'method' => 'post', 'id' => 'adjust_form']) !!}
                
                <div class="box-body">
                    
                    {{-- Alerte Solde Actuel --}}
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Compte :</strong> {{ $account->account_name }} ({{ $account->account_number }})
                        <br>
                        <strong>Solde actuel :</strong> 
                        <span class="pull-right" style="font-size: 20px;">
                            <strong>{{ number_format($account->balance, 0, ',', ' ') }} FCFA</strong>
                        </span>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Attention :</strong> Cette fonction est réservée aux <strong>corrections d'erreurs comptables</strong>.
                    </div>
                    
                    <div class="row">
                        {{-- Type d'ajustement --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type d'ajustement <span class="text-danger">*</span></label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="credit" checked>
                                        <span class="text-success"><strong>➕ Augmenter le solde</strong></span>
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="debit">
                                        <span class="text-danger"><strong>➖ Diminuer le solde</strong></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Montant --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Montant <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                    {!! Form::number('amount', null, [
                                        'class' => 'form-control',
                                        'required' => true,
                                        'min' => 1,
                                        'step' => 1,
                                        'id' => 'amount'
                                    ]) !!}
                                    <span class="input-group-addon"><strong>FCFA</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Motif --}}
                    <div class="form-group">
                        <label for="reason">Motif <span class="text-danger">*</span></label>
                        {!! Form::textarea('reason', null, [
                            'class' => 'form-control',
                            'placeholder' => 'Ex: Correction erreur saisie du 28/01',
                            'required' => true,
                            'rows' => 4,
                            'minlength' => 5,
                            'id' => 'reason'
                        ]) !!}
                        <small class="text-muted">Minimum 5 caractères</small>
                    </div>
                    
                    {{-- Aperçu --}}
                    <div class="alert alert-success" id="balance_preview" style="display:none;">
                        <strong>Nouveau solde :</strong> <span id="new_balance">0</span> FCFA
                    </div>
                    
                </div>
                
                <div class="box-footer">
                    <a href="{{ url()->previous() }}" class="btn btn-default btn-lg">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-warning btn-lg pull-right">
                        Confirmer
                    </button>
                </div>
                
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var currentBalance = {{ $account->balance }};
    
    function updatePreview() {
        var amount = parseFloat($('#amount').val()) || 0;
        var type = $('input[name="type"]:checked').val();
        
        if (amount > 0) {
            var newBalance = type === 'credit' ? currentBalance + amount : currentBalance - amount;
            $('#new_balance').text(newBalance.toLocaleString('fr-FR'));
            $('#balance_preview').slideDown();
        } else {
            $('#balance_preview').slideUp();
        }
    }
    
    $('#amount').on('input', updatePreview);
    $('input[name="type"]').on('change', updatePreview);
});
</script>
@endsection