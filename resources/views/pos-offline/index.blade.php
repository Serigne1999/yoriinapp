@extends('layouts.app')

@section('title', 'POS Offline')

@section('content')
<div class="pos-offline-container" style="height: calc(100vh - 100px); overflow: hidden;">
    <div class="row" style="height: 100%; margin: 0;">
        <!-- Colonne Produits (gauche) -->
        <div class="col-md-8" style="height: 100%; overflow-y: auto; padding: 15px; background: #f4f4f4;">
            <!-- Header recherche -->
            <div class="box box-primary" style="margin-bottom: 15px;">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" 
                                   id="product-search" 
                                   class="form-control input-lg" 
                                   placeholder="🔍 Rechercher un produit (nom, code-barres...)"
                                   autofocus>
                        </div>
                        <div class="col-md-4">
                            <select id="location-filter" class="form-control input-lg">
                                <option value="">📍 Toutes les locations</option>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status sync -->
            <div id="sync-status" class="alert alert-info" style="display: none;">
                <i class="fa fa-sync-alt fa-spin"></i> <span id="sync-message">Chargement des produits...</span>
            </div>

            <!-- Grille produits -->
            <div id="products-grid" class="row">
                <!-- Produits seront chargés ici par JavaScript -->
            </div>

            <!-- Loading -->
            <div id="products-loading" class="text-center" style="padding: 50px;">
                <i class="fa fa-spinner fa-spin fa-3x"></i>
                <p style="margin-top: 20px;">Chargement des produits en cache...</p>
            </div>

            <!-- Aucun produit -->
            <div id="no-products" class="text-center" style="display: none; padding: 50px;">
                <i class="fa fa-inbox fa-3x text-muted"></i>
                <p style="margin-top: 20px; font-size: 18px;">Aucun produit en cache</p>
                <button onclick="window.posSync.syncProducts()" class="btn btn-primary">
                    <i class="fa fa-sync-alt"></i> Synchroniser maintenant
                </button>
            </div>
        </div>

        <!-- Colonne Panier (droite) -->
        <div class="col-md-4" style="height: 100%; background: white; border-left: 2px solid #ddd; display: flex; flex-direction: column; padding: 0;">
            <!-- Header panier -->
            <div style="padding: 15px; border-bottom: 2px solid #3c8dbc; background: #f9f9f9;">
                <h3 style="margin: 0;">
                    <i class="fa fa-shopping-cart"></i> Panier
                    <span id="cart-count" class="label label-primary" style="font-size: 14px; margin-left: 10px;">0</span>
                </h3>
            </div>

            <!-- Items panier -->
            <div id="cart-items" style="flex: 1; overflow-y: auto; padding: 15px;">
                <div id="empty-cart" class="text-center text-muted" style="padding: 50px 20px;">
                    <i class="fa fa-shopping-cart fa-3x"></i>
                    <p style="margin-top: 20px;">Panier vide</p>
                    <small>Cliquez sur un produit pour l'ajouter</small>
                </div>
            </div>

            <!-- Footer panier (total + actions) -->
            <div style="border-top: 2px solid #ddd; padding: 15px; background: #f9f9f9;">
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; font-size: 24px; font-weight: bold;">
                        <span>TOTAL :</span>
                        <span id="cart-total" style="color: #00a65a;">0 FCFA</span>
                    </div>
                </div>

                <button id="btn-finalize" 
                        class="btn btn-success btn-lg btn-block" 
                        style="font-size: 18px; padding: 15px;"
                        disabled>
                    <i class="fa fa-check"></i> Finaliser la vente
                </button>

                <button id="btn-clear-cart" 
                        class="btn btn-default btn-block" 
                        style="margin-top: 10px;">
                    <i class="fa fa-trash"></i> Vider le panier
                </button>

                <!-- Indicateur offline -->
                <div id="offline-indicator" class="text-center" style="margin-top: 10px; display: none;">
                    <small class="text-danger">
                        <i class="fa fa-wifi"></i> Mode offline - Vente sera synchronisée plus tard
                    </small>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts POS Offline -->
    <script src="{{ asset('js/pos-db.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/pos-offline.js') }}?v={{ time() }}"></script>
    
    <script>
    console.log('✅ Scripts loaded');
    console.log('posDB:', typeof window.posDB);
    console.log('POSDatabase:', typeof POSDatabase);
    console.log('POSOfflineApp:', typeof POSOfflineApp);
    </script>
</div>


@endsection