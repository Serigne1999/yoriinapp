@extends('layouts.app')

@section('title', 'Prélèvement Caisse')

@section('content')
<section class="content-header">
    <h1>Prélèvement Caisse {{ $register->id }}
        <small>{{ $register->business->name }}</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-minus-circle"></i> Prélever de la caisse
                    </h3>
                </div>
                
                {!! Form::open(['route' => ['cash-register.expense.process', $register->id], 'method' => 'post', 'id' => 'expense_form']) !!}
                
                <div class="box-body">
                    
                    {{-- Alerte Solde Actuel --}}
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Solde actuel de la caisse :</strong> 
                        <span class="pull-right" style="font-size: 18px;">
                            <strong>{{ number_format($register->expected_amount, 0, ',', ' ') }} FCFA</strong>
                        </span>
                    </div>
                    
                    <div class="row">
                        {{-- Montant --}}
                        <div class="col-md-6">
                            <div class="form-group @error('amount') has-error @enderror">
                                <label for="amount">
                                    Montant <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-money"></i>
                                    </span>
                                    {!! Form::number('amount', null, [
                                        'class' => 'form-control input-lg',
                                        'placeholder' => 'Montant',
                                        'required' => true,
                                        'min' => 1,
                                        'step' => 1,
                                        'id' => 'amount',
                                        'autofocus' => true
                                    ]) !!}
                                    <span class="input-group-addon"><strong>FCFA</strong></span>
                                </div>
                                @error('amount')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        {{-- Bénéficiaire --}}
                        <div class="col-md-6">
                            <div class="form-group @error('beneficiary') has-error @enderror">
                                <label for="beneficiary">
                                    Bénéficiaire <small class="text-muted">(optionnel)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    {!! Form::text('beneficiary', null, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Ex: Boutique Diop, Fournisseur...',
                                        'maxlength' => 200,
                                        'id' => 'beneficiary'
                                    ]) !!}
                                </div>
                                @error('beneficiary')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    {{-- Motif --}}
                    <div class="form-group @error('motif') has-error @enderror">
                        <label for="motif">
                            Motif <span class="text-danger">*</span>
                        </label>
                        {!! Form::textarea('motif', null, [
                            'class' => 'form-control',
                            'placeholder' => 'Ex: Achat fournitures bureau, Paiement fournisseur, Dépense carburant...',
                            'required' => true,
                            'rows' => 3,
                            'minlength' => 5,
                            'maxlength' => 500,
                            'id' => 'motif'
                        ]) !!}
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i> Minimum 5 caractères
                        </small>
                        @error('motif')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    {{-- Aperçu nouveau solde --}}
                    <div class="alert alert-warning" id="balance_preview" style="display:none;">
                        <div class="row">
                            <div class="col-xs-6">
                                <i class="fa fa-calculator"></i>
                                <strong>Nouveau solde :</strong>
                            </div>
                            <div class="col-xs-6 text-right">
                                <span id="new_balance" style="font-size: 18px; font-weight: bold;">0</span> FCFA
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="box-footer">
                    <a href="{{ route('cash-register.show', $register->id) }}" 
                       class="btn btn-default btn-lg">
                        <i class="fa fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-warning btn-lg pull-right" id="submit_btn">
                        <i class="fa fa-minus-circle"></i> Prélever
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
    var currentBalance = {{ $register->expected_amount }};
    
    // Calcul aperçu temps réel
    $('#amount').on('input', function() {
        var amount = parseFloat($(this).val()) || 0;
        
        if (amount > 0) {
            var newBalance = currentBalance - amount;
            $('#new_balance').text(newBalance.toLocaleString('fr-FR'));
            
            // Changement couleur si négatif
            if (newBalance < 0) {
                $('#balance_preview')
                    .removeClass('alert-warning')
                    .addClass('alert-danger');
                $('#new_balance').css('color', '#a94442');
            } else {
                $('#balance_preview')
                    .removeClass('alert-danger')
                    .addClass('alert-warning');
                $('#new_balance').css('color', '#8a6d3b');
            }
            
            $('#balance_preview').slideDown();
        } else {
            $('#balance_preview').slideUp();
        }
    });
    
    // Validation avant soumission
    $('#expense_form').on('submit', function(e) {
        var amount = parseFloat($('#amount').val()) || 0;
        var motif = $('#motif').val().trim();
        
        // Vérification solde
        if (amount > currentBalance) {
            e.preventDefault();
            swal({
                title: 'Solde insuffisant',
                text: 'Solde actuel : ' + currentBalance.toLocaleString('fr-FR') + ' FCFA',
                type: 'error'
            });
            return false;
        }
        
        // Vérification motif
        if (motif.length < 5) {
            e.preventDefault();
            swal({
                title: 'Motif invalide',
                text: 'Le motif doit contenir au moins 5 caractères',
                type: 'error'
            });
            return false;
        }
        
        // Désactiver bouton pour éviter double soumission
        $('#submit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Traitement...');
    });
});
</script>
@endsection