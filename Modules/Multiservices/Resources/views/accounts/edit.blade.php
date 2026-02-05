@extends('layouts.app')

@section('title', 'Modifier le compte')

@section('content')
<section class="content-header">
    <h1>Modifier le compte
        <small>{{ $account->operator_name }}</small>
    </h1>
</section>

<section class="content">
    <form action="{{ route('multiservices.accounts.update', $account->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informations du compte</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Opérateur</label>
                            <input type="text" class="form-control" value="{{ $account->operator_name }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Nom du compte *</label>
                            <input type="text" name="account_name" class="form-control" value="{{ $account->account_name }}" required>
                        </div>

                        <div class="form-group">
                            <label>Numéro de compte *</label>
                            <input type="text" name="account_number" class="form-control" value="{{ $account->account_number }}" required>
                        </div>

                        <div class="form-group">
                            <label>Solde actuel</label>
                            <input type="text" class="form-control" value="{{ number_format($account->balance, 0) }} FCFA" disabled>
                            <small class="text-muted">Le solde ne peut pas être modifié directement. Utilisez "Alimenter" pour ajuster le solde.</small>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $account->notes }}</textarea>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" {{ $account->is_active ? 'checked' : '' }}>
                                Compte actif
                            </label>
                        </div>
                    </div>
                    
                    <div class="box-footer">
                        <a href="{{ route('multiservices.accounts.index') }}" class="btn btn-default">Annuler</a>
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
