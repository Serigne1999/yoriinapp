@extends('layouts.app')
@section('title', 'Ouvrir une caisse')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Ouvrir une caisse Multiservices</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Nouvelle ouverture de caisse</h3>
                </div>
                
                <form action="{{ route('cash-register.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="row">
                            <!-- Location -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Location *</label>
                                    @if(count($locations) > 1)
                                        <select name="location_id" class="form-control" required>
                                            @foreach($locations as $id => $name)
                                            <option value="{{ $id }}" {{ (auth()->user()->location_id == $id) ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    @else
                                        @php
                                            $userLocationId = auth()->user()->location_id ?? array_key_first($locations->toArray());
                                            $locationName = $locations[$userLocationId] ?? 'N/A';
                                        @endphp
                                        <input type="text" class="form-control" value="{{ $locationName }}" readonly>
                                        <input type="hidden" name="location_id" value="{{ $userLocationId }}">
                                    @endif
                                    <p class="help-block">La caisse sera ouverte pour cette location</p>
                                </div>
                            </div>

                            <!-- Agent -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Ouvert par *</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" readonly>
                                    <p class="help-block">Vous êtes l'agent responsable de cette caisse</p>
                                </div>
                            </div>

                            <!-- Montant d'ouverture -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Montant d'ouverture (FCFA) *</label>
                                    <input type="number" name="opening_amount" class="form-control" required min="0" step="1" placeholder="Ex: 100000">
                                    <p class="help-block">Le fond de départ de la caisse</p>
                                </div>
                            </div>

                            <!-- Notes d'ouverture -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Notes d'ouverture</label>
                                    <textarea name="opening_notes" class="form-control" rows="3" placeholder="Notes optionnelles..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('cash-register.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary pull-right">
                            <i class="fa fa-lock-open"></i> Ouvrir la caisse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection