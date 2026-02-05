@extends('layouts.app')
@section('title', 'QR Codes des Tables Restaurant')

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fa fa-qrcode"></i> QR Codes des Tables Restaurant
    </h1>
</section>

<section class="content">
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        <strong>Info :</strong> Les tables sont gérées dans <a href="{{url('/modules/tables')}}" target="_blank">Paramètres → Les tables</a>. 
        Ici vous pouvez générer et imprimer les QR codes pour chaque table.
    </div>

    @component('components.widget', ['class' => 'box-primary'])
        @slot('title')
            <i class="fa fa-table"></i> Vos Tables
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="tables_table">
                <thead>
                    <tr>
                        <th>Nom de la table</th>
                        <th>Description</th>
                        <th>Lieu</th>
                        <th>QR Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">QR Code - <span id="modal_table_name"></span></h4>
            </div>
            <div class="modal-body text-center">
                <div id="qr_display"></div>
                <div style="margin-top: 20px;">
                    <h4>Lien du catalogue</h4>
                    <input type="text" id="catalogue_link" class="form-control" readonly style="text-align: center;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" id="download_qr">
                    <i class="fa fa-download"></i> Télécharger QR
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fa fa-print"></i> Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('modules/productcatalogue/plugins/easy.qrcode.min.js') }}"></script>
<script>
$(document).ready(function(){
    var tables_table = $('#tables_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'getResTables'])}}",
        columns: [
            {data: 'name', name: 'name'},
            {data: 'description', name: 'description'},
            {data: 'location_name', name: 'location_name'},
            {data: 'qr_preview', name: 'qr_preview', orderable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[0, 'asc']]
    });

    // Générer et afficher le QR Code
    $(document).on('click', '.view-qr', function(){
        var tableId = $(this).data('table-id');
        var tableName = $(this).data('table-name');
        var businessId = $(this).data('business-id');
        var locationId = $(this).data('location-id');

        var catalogueUrl = "{{url('/catalogue')}}/" + businessId + "/" + locationId + "/" + tableId;

        $('#modal_table_name').text(tableName);
        $('#catalogue_link').val(catalogueUrl);
        $('#qr_display').html('');

        // Générer le QR
        var opts = {
            text: catalogueUrl,
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H,
            title: tableName,
            titleFont: "bold 18px Arial",
            titleColor: "#004284",
            titleBackgroundColor: "#ffffff",
            titleHeight: 60,
            titleTop: 20
        };

        new QRCode(document.getElementById("qr_display"), opts);
        
        $('#qrModal').modal('show');

        // Attendre que le canvas soit créé
        setTimeout(function(){
            $('#qr_display canvas').attr('id', 'qr_canvas_' + tableId);
        }, 500);
    });

    // Télécharger le QR
    $('#download_qr').click(function(){
        var canvas = $('#qr_display canvas')[0];
        if(canvas){
            var link = document.createElement('a');
            link.download = 'qr-table-' + $('#modal_table_name').text() + '.png';
            link.href = canvas.toDataURL();
            link.click();
        }
    });

    // Imprimer tous les QR codes
    $(document).on('click', '#print_all_qr', function(){
        window.open("{{action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'printAllTableQR'])}}", '_blank');
    });
});
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #qr_display, #qr_display * {
        visibility: visible;
    }
    #qr_display {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }
}
</style>
@endsection