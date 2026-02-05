@extends('layouts.app')

@section('title', 'Nouvelle Transaction')

@section('content')
<section class="content-header">
    <h1>Nouvelle Transaction Multiservices</h1>
</section>

<section class="content">
    <form action="{{ route('multiservices.store') }}" method="POST">
        @csrf
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Informations de la transaction</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Opérateur *</label>
                                    <select name="operator" id="operator" class="form-control" required>
                                        <option value="">Sélectionner...</option>
                                        @foreach($operators as $key => $op)
                                        <option value="{{ $key }}">{{ $op }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type de transaction *</label>
                                    <select name="transaction_type" id="transaction_type" class="form-control" required>
                                        <option value="">Sélectionner...</option>
                                        @foreach($transactionTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">  <!-- ← AJOUTER -->
                                <div class="form-group">
                                    <label>Location *</label>
                                    <select name="location_id" id="location_id" class="form-control" required>
                                        <option value="">Sélectionner</option>
                                        @foreach($locations as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- NOUVEAU CHAMP : Sélection du compte -->
                                <div class="form-group" id="account_field" style="display: none;">
                                    <label>Compte * <span class="text-muted" id="account_loading" style="display:none;"><i class="fa fa-spinner fa-spin"></i> Chargement...</span></label>
                                    <select name="operator_account_id" id="operator_account_id" class="form-control" required>
                                        <option value="">Sélectionner un compte</option>
                                    </select>
                                    <span class="help-block text-danger" id="no_accounts_warning" style="display:none;">
                                        <i class="fa fa-exclamation-triangle"></i> Aucun compte actif trouvé pour cet opérateur dans cette location.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Montant *</label>
                                    <input type="text" name="amount" id="amount" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>

                        <!-- Expéditeur -->
                        <div id="sender_section" style="display:none;">
                            <h4>Expéditeur</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom complet</label>
                                        <input type="text" name="sender_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="text" name="sender_phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Destinataire -->
                        <div id="receiver_section" style="display:none;">
                            <h4>Destinataire</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nom complet</label>
                                        <input type="text" name="receiver_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="text" name="receiver_phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panneau de calcul -->
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header">
                        <h3 class="box-title">Calcul automatique</h3>
                    </div>
                    <div class="box-body">
                        <table class="table">
                            <tr>
                                <th>Montant :</th>
                                <td id="display_amount">0 FCFA</td>
                            </tr>
                            <tr>
                                <th>Frais :</th>
                                <td id="display_fee">0 FCFA</td>
                            </tr>
                            <tr class="text-bold">
                                <th>Total :</th>
                                <td id="display_total">0 FCFA</td>
                            </tr>
                            <tr class="text-success">
                                <th>Bénéfice :</th>
                                <td id="display_profit">0 FCFA</td>
                            </tr>
                        </table>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-save"></i> Enregistrer
                            </button>
                        </div>

                        <a href="{{ route('multiservices.index') }}" class="btn btn-default btn-block">
                            <i class="fa fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Afficher/masquer sections selon le type
    $('#transaction_type').change(function() {
        var type = $(this).val();
        
        $('#sender_section').hide();
        $('#receiver_section').hide();
        
        if (type === 'deposit') {
            $('#receiver_section').show();
        } else if (type === 'withdrawal') {
            $('#sender_section').show();
        } else if (type === 'transfer') {
            $('#sender_section').show();
            $('#receiver_section').show();
        }
        
        calculateFees();
    });

    // Calcul automatique des frais
    $('#operator, #transaction_type, #amount').on('change keyup', function() {
        calculateFees();
    });

    function calculateFees() {
        var operator = $('#operator').val();
        var transactionType = $('#transaction_type').val();
        var amount = $('#amount').val().replace(/\s/g, '').replace(',', '.');

        if (!operator || !transactionType || !amount || amount <= 0) {
            $('#display_amount').text('0 FCFA');
            $('#display_fee').text('0 FCFA');
            $('#display_total').text('0 FCFA');
            $('#display_profit').text('0 FCFA');
            return;
        }

        $.ajax({
            url: '{{ route("multiservices.calculate-fees") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                operator: operator,
                transaction_type: transactionType,
                amount: amount
            },
            success: function(result) {
                $('#display_amount').text(amount + ' FCFA');
                $('#display_fee').text(result.fee + ' FCFA');
                $('#display_total').text(result.total + ' FCFA');
                $('#display_profit').text(result.profit + ' FCFA');
            }
        });
    }
});
</script>
<script>
$(document).ready(function() {
    
    // Quand l'opérateur ou la location change, charger les comptes
    $('#operator, #location_id').on('change', function() {
        loadAccounts();
    });
    
    function loadAccounts() {
        const operator = $('#operator').val();
        const locationId = $('#location_id').val();
        
        if (!operator || !locationId) {
            $('#account_field').hide();
            return;
        }
        
        // Afficher le chargement
        $('#account_loading').show();
        $('#operator_account_id').prop('disabled', true).html('<option value="">Chargement...</option>');
        $('#no_accounts_warning').hide();
        $('#account_field').show();
        
        // Requête AJAX
        $.ajax({
            url: '/multiservices/get-accounts/' + operator,
            data: { location_id: locationId },
            success: function(accounts) {
                $('#account_loading').hide();
                $('#operator_account_id').prop('disabled', false);
                
                if (accounts.length === 0) {
                    $('#operator_account_id').html('<option value="">Aucun compte disponible</option>');
                    $('#no_accounts_warning').show();
                } else {
                    let options = '<option value="">Sélectionner un compte</option>';
                    accounts.forEach(function(account) {
                        options += '<option value="' + account.id + '">' + account.label + '</option>';
                    });
                    $('#operator_account_id').html(options);
                    
                    // Si un seul compte, le sélectionner automatiquement
                    if (accounts.length === 1) {
                        $('#operator_account_id').val(accounts[0].id);
                    }
                }
            },
            error: function() {
                $('#account_loading').hide();
                $('#operator_account_id').prop('disabled', false).html('<option value="">Erreur de chargement</option>');
                swal('Erreur', 'Impossible de charger les comptes', 'error');
            }
        });
    }
    
    // Charger les comptes au chargement si opérateur déjà sélectionné
    if ($('#operator').val() && $('#location_id').val()) {
        loadAccounts();
    }
});
</script>
<script>
// Auto-sélection location unique
$(document).ready(function() {
    var $locationSelect = $('#location_id');
    var $formGroup = $locationSelect.closest('.form-group');
    var locationCount = $locationSelect.find('option:not([value=""])').length;
    
    if (locationCount === 1) {
        // Auto-sélectionner la location unique
        var singleLocationId = $locationSelect.find('option:not([value=""])').first().val();
        var locationName = $locationSelect.find('option:not([value=""])').first().text();
        
        $locationSelect.val(singleLocationId).trigger('change');
        
        // Masquer le select
        $formGroup.slideUp(200);
        
        // Afficher un indicateur propre
        $formGroup.after(
            '<div class="location-auto-indicator" style="margin-bottom: 15px;">' +
                '<div style="background: #d1ecf1; border-left: 4px solid #0c5460; padding: 10px 15px; border-radius: 4px;">' +
                    '<i class="fa fa-map-marker-alt" style="color: #0c5460; margin-right: 8px;"></i>' +
                    '<span style="color: #0c5460; font-weight: 500;">Emplacement : ' + locationName + '</span>' +
                '</div>' +
            '</div>'
        );
        
        console.log('✅ Location auto-sélectionnée : ' + locationName);
    }
});
</script>
@endsection
