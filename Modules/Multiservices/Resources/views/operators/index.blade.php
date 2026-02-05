@extends('layouts.app')

@section('title', 'Gestion des Opérateurs')

@section('content')
@include('multiservices::layouts.nav')

<section class="content-header">
    <h1>Gestion des Opérateurs</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Liste des opérateurs</h3>
            <div class="box-tools">
                <button class="btn btn-primary" data-toggle="modal" data-target="#add_operator_modal">
                    <i class="fa fa-plus"></i> Ajouter un opérateur
                </button>
            </div>
        </div>
        <div class="box-body" style="overflow: visible;">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="operators_table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Code</th>
                            <th>Icône</th>
                            <th>Ordre</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal : Ajouter -->
<div class="modal fade" id="add_operator_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add_operator_form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Ajouter un opérateur</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Wave, Orange Money" required>
                    </div>

                    <div class="form-group">
                        <label>Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="Ex: wave, orange_money" required>
                        <small class="text-muted">Uniquement lettres minuscules, chiffres et tirets bas</small>
                    </div>

                    <div class="form-group">
                        <label>Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp">
                            <small class="text-muted">PNG, JPG, SVG, WebP - Max 2 MB</small>
                    </div>

                    <div class="form-group">
                        <label>OU Icône FontAwesome (si pas de logo)</label>
                        <input type="text" name="icon" class="form-control" placeholder="Ex: fa-mobile">
                        <small class="text-muted">Voir <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com</a></small>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal : Modifier -->
<div class="modal fade" id="edit_operator_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit_operator_form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="operator_id" id="edit_operator_id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Modifier l'opérateur</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" id="edit_code" class="form-control" disabled>
                        <small class="text-muted">Le code ne peut pas être modifié</small>
                    </div>

                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Logo actuel</label>
                        <div id="current_logo"></div>
                    </div>

                    <div class="form-group">
                        <label>Changer le logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                        <small class="text-muted">PNG, JPG, SVG - Max 2 MB</small>
                    </div>

                    <div class="form-group">
                        <label>OU Icône FontAwesome</label>
                        <input type="text" name="icon" id="edit_icon" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Ordre d'affichage</label>
                        <input type="number" name="display_order" id="edit_display_order" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // DataTable
    var table = $('#operators_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("multiservices.operators.index") }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'logo', name: 'logo' },
            { data: 'display_order', name: 'display_order' },
            { data: 'is_active', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Ajouter
    $('#add_operator_form').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("multiservices.operators.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(result) {
                if (result.success) {
                    $('#add_operator_modal').modal('hide');
                    toastr.success(result.msg);
                    table.ajax.reload();
                    $('#add_operator_form')[0].reset();
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                toastr.error('Une erreur est survenue');
            }
        });
    });

    // Modifier - Charger les données
    $(document).on('click', '.edit-operator', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        // Vider le formulaire d'abord
        $('#edit_operator_form')[0].reset();
        $('#current_logo').html('<span class="text-muted">Chargement...</span>');
        
        $.get('/multiservices/operators/' + id, function(result) {
            if (result.success) {
                $('#edit_operator_id').val(result.data.id);
                $('#edit_code').val(result.data.code);
                $('#edit_name').val(result.data.name);
                $('#edit_icon').val(result.data.icon || '');
                $('#edit_display_order').val(result.data.display_order);
                $('#edit_description').val(result.data.description || '');
                
                // Afficher logo actuel (gérer URL externe et fichier local)
                if (result.data.logo) {
                    var logoSrc = result.data.logo.indexOf('http') === 0 
                        ? result.data.logo 
                        : '/' + result.data.logo;
                    
                    $('#current_logo').html('<img src="' + logoSrc + '" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px; border-radius: 8px; background: white;">');
                } else {
                    $('#current_logo').html('<span class="text-muted">Aucun logo</span>');
                }
                
                $('#edit_operator_modal').modal('show');
            }
        }).fail(function(xhr) {
            console.error('Erreur:', xhr.responseText);
            toastr.error('Erreur lors du chargement');
        });
    });

    // Modifier - Enregistrer
    $('#edit_operator_form').submit(function(e) {
        e.preventDefault();
        var id = $('#edit_operator_id').val();
        var form = this;
        
        // Créer FormData manuellement
        var formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('name', form.querySelector('[name="name"]').value);
        formData.append('icon', form.querySelector('[name="icon"]').value || '');
        formData.append('description', form.querySelector('[name="description"]').value || '');
        formData.append('display_order', form.querySelector('[name="display_order"]').value || '');
        
        // Ajouter le logo SEULEMENT s'il y a un fichier
        var logoInput = form.querySelector('[name="logo"]');
        if (logoInput.files.length > 0) {
            formData.append('logo', logoInput.files[0]);
        }
        
        $.ajax({
            url: '/multiservices/operators/' + id,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(result) {
                if (result.success) {
                    $('#edit_operator_modal').modal('hide');
                    toastr.success(result.msg);
                    table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                console.error('Erreur update:', xhr.responseText);
                toastr.error('Une erreur est survenue');
            }
        });
    });

    // Toggle actif/inactif
    $(document).on('click', '.toggle-operator', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $.ajax({
            url: '/multiservices/operators/' + id + '/toggle',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    // Supprimer
    $(document).on('click', '.delete-operator', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        swal({
            title: 'Confirmer',
            text: 'Supprimer cet opérateur ? Cette action est irréversible.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '/multiservices/operators/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            table.ajax.reload();
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
