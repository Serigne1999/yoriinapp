@extends('layouts.app')
@section('title', __('Commandes Catalogue'))

@section('content')
<section class="content-header">
    <h1>@lang('Commandes en ligne')
        <small>@lang('Gestion des commandes depuis le catalogue')</small>
    </h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('Toutes les commandes')])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-sm" data-toggle="modal" data-target="#filterModal">
                    <i class="fa fa-filter"></i> @lang('Filtrer')
                </button>
            </div>
        @endslot
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="orders_table">
                <thead>
                    <tr>
                        <th>@lang('N° Commande')</th>
                        <th>@lang('Date')</th>
                        <th>@lang('Client')</th>
                        <th>@lang('Téléphone')</th>
                        <th>@lang('Montant')</th>
                        <th>@lang('Statut')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>

<!-- Modal Filtre -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">@lang('Filtrer les commandes')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('Statut'):</label>
                    <select class="form-control" id="status_filter">
                        <option value="">@lang('Tous')</option>
                        <option value="pending">@lang('En attente')</option>
                        <option value="paid">@lang('Payé')</option>
                        <option value="processing">@lang('En préparation')</option>
                        <option value="delivered">@lang('Livré')</option>
                        <option value="cancelled">@lang('Annulé')</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>@lang('Date de'):</label>
                    <input type="date" class="form-control" id="date_from">
                </div>
                <div class="form-group">
                    <label>@lang('Date à'):</label>
                    <input type="date" class="form-control" id="date_to">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Fermer')</button>
                <button type="button" class="btn btn-primary" id="applyFilter">@lang('Appliquer')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
    var orders_table = $('#orders_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orders'])}}",
            data: function(d){
                d.status = $('#status_filter').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [
            {data: 'order_number', name: 'order_number'},
            {data: 'created_at', name: 'created_at'},
            {data: 'customer_name', name: 'customer_name'},
            {data: 'customer_phone', name: 'customer_phone'},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });

    // Appliquer les filtres
    $('#applyFilter').click(function(){
        orders_table.ajax.reload();
        $('#filterModal').modal('hide');
    });

    // Supprimer une commande
    $(document).on('click', '.delete-order', function(e){
        e.preventDefault();
        var url = $(this).data('href');
        
        swal({
            title: "@lang('Êtes-vous sûr?')",
            text: "@lang('Cette action est irréversible')",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    dataType: 'json',
                    data: {_token: "{{csrf_token()}}"},
                    success: function(result){
                        if(result.success){
                            toastr.success(result.msg);
                            orders_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection