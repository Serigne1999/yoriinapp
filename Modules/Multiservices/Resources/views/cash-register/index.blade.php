@extends('layouts.app')
@section('title', 'Caisses Multiservices')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Caisses Multiservices <small>Gestion séparée</small></h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title">Liste des caisses</h3>
            <div class="box-tools">
                <a href="{{ route('cash-register.create') }}" class="btn btn-success">
                    <i class="fa fa-plus"></i> Ouvrir une caisse
                </a>
            </div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr class="bg-gray">
                            <th>Date</th>
                            <th>Location</th>
                            <th>Ouvert par</th>
                            <th>Montant ouverture</th>
                            <th>Montant attendu</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registers as $register)
                        <tr>
                            <td>{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $register->location->name ?? 'N/A' }}</td>
                            <td>{{ $register->user->username ?? 'N/A' }}</td>
                            <td>{{ number_format($register->opening_amount, 0) }} FCFA</td>
                            <td class="text-bold text-blue">{{ number_format($register->expected_amount, 0) }} FCFA</td>
                            <td>
                                @if($register->status === 'open')
                                    <span class="label label-success">Ouverte</span>
                                @else
                                    <span class="label label-default">Fermée</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('cash-register.show', $register->id) }}" class="btn btn-xs btn-info">
                                    <i class="fa fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                                <i class="fa fa-info-circle fa-2x"></i><br><br>
                                Aucune caisse trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $registers->links() }}
        </div>
    </div>
</section>
@endsection
