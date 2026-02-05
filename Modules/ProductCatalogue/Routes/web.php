<?php

use Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController;
use Modules\ProductCatalogue\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Routes pour le panier (public, pas besoin d'auth)
Route::prefix('catalogue/{business_id}/{location_id}')->group(function () {
    Route::post('/cart/add', [ProductCatalogueController::class, 'addToCart'])->name('catalogue.cart.add');
    Route::post('/cart/update', [ProductCatalogueController::class, 'updateCart'])->name('catalogue.cart.update');
    Route::post('/cart/remove', [ProductCatalogueController::class, 'removeFromCart'])->name('catalogue.cart.remove');
    Route::get('/cart/get', [ProductCatalogueController::class, 'getCart'])->name('catalogue.cart.get');
    Route::post('/cart/clear', [ProductCatalogueController::class, 'clearCart'])->name('catalogue.cart.clear');
    // Checkout et commande
    Route::get('/checkout', [ProductCatalogueController::class, 'checkout'])->name('catalogue.checkout');
    Route::post('/order/submit', [ProductCatalogueController::class, 'submitOrder'])->name('catalogue.order.submit');
    Route::get('/order/confirmation/{order_id}', [ProductCatalogueController::class, 'orderConfirmation'])->name('catalogue.order.confirmation');
});

// Routes publiques du catalogue
Route::get('/catalogue/{business_id}/{location_id}/{table_id?}', [ProductCatalogueController::class, 'index']);
Route::get('/show-catalogue/{business_id}/{product_id}', [ProductCatalogueController::class, 'show']);

// Routes protégées (admin seulement)
Route::middleware(['web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])
    ->prefix('product-catalogue')
    ->group(function () {
        // Génération QR
        Route::get('catalogue-qr', [ProductCatalogueController::class, 'generateQr']);
        Route::post('product-catalogue-setting', [ProductCatalogueController::class, 'productCatalogueSetting']);
        
        // Vérification nouvelles commandes (notifications)
        Route::get('check-new-orders', [ProductCatalogueController::class, 'checkNewOrders'])->name('catalogue.check_new_orders');
        
        // Gestion des commandes
        Route::get('orders', [ProductCatalogueController::class, 'orders'])->name('productcatalogue.orders');
        Route::get('orders/{id}', [ProductCatalogueController::class, 'orderDetails'])->name('productcatalogue.orders.details');
        Route::post('orders/{id}/update-status', [ProductCatalogueController::class, 'updateOrderStatus'])->name('productcatalogue.orders.update_status');
        Route::delete('orders/{id}', [ProductCatalogueController::class, 'deleteOrder'])->name('productcatalogue.orders.delete');
        
        // Préparer commande pour POS
        Route::post('orders/{id}/prepare-pos', [ProductCatalogueController::class, 'prepareOrderForPOS'])->name('productcatalogue.orders.prepare_pos');
        
        // Installation
        Route::get('install', [InstallController::class, 'index']);
        Route::post('install', [InstallController::class, 'install']);
        Route::get('install/uninstall', [InstallController::class, 'uninstall']);
        Route::get('install/update', [InstallController::class, 'update']);
        
        // Gestion QR codes des tables restaurant
        Route::get('restaurant-tables', [ProductCatalogueController::class, 'restaurantTables'])->name('productcatalogue.restaurant_tables');
        Route::get('restaurant-tables/list', [ProductCatalogueController::class, 'getResTables'])->name('productcatalogue.restaurant_tables.list');
        Route::get('restaurant-tables/print-all', [ProductCatalogueController::class, 'printAllTableQR'])->name('productcatalogue.restaurant_tables.print_all');
    });