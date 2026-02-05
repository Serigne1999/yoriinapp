// Synchronisation automatique des produits
class POSSync {
    constructor() {
        this.isSyncing = false;
        this.syncInterval = 1000 * 60 * 60; // 1 heure
    }

    async syncProducts() {
        if (this.isSyncing) {
            console.log('⏳ Sync already in progress...');
            return;
        }

        this.isSyncing = true;
        this.showSyncIndicator('Synchronisation des produits...');

        try {
            console.log('📥 Fetching products from server...');
            
            // Essayer plusieurs endpoints
            let products = [];
            let success = false;
            
            // Méthode 1 : Endpoint direct
            try {
                const response = await fetch('/products/list?type=product&format=json', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    products = data.data || data;
                    
                    // Normaliser les produits
                    products = products.map(p => {
                        if (!p.id && p.product_id) p.id = p.product_id;
                        if (!p.id && p.variation_id) p.id = p.variation_id;
                        if (!p.id) p.id = 'p_' + Math.random().toString(36).substr(2, 9);
                        return p;
                    }).filter(p => p.id);
                    
                    success = true;
                    console.log(`📦 Method 1: ${products.length} products`);
                }
            } catch (e) {
                console.log('Method 1 failed:', e);
            }
            
            // Méthode 2 : Utiliser les produits déjà en mémoire du POS
            if (!success && typeof product_stock_data !== 'undefined') {
                products = Object.values(product_stock_data).map(p => {
                    if (!p.id) p.id = p.product_id || p.variation_id || 'p_' + Math.random().toString(36).substr(2, 9);
                    return p;
                });
                success = true;
                console.log(`📦 Method 2 (POS memory): ${products.length} products`);
            }

            if (!success || products.length === 0) {
                throw new Error('Aucun produit trouvé');
            }

            console.log(`✅ Total: ${products.length} products to sync`);

            // Sauvegarder dans IndexedDB
            const count = await window.posDB.syncProducts(products);

            this.showSyncSuccess(`✅ ${count} produits synchronisés`);
            
            window.dispatchEvent(new CustomEvent('products-synced', { 
                detail: { count: count } 
            }));

        } catch (error) {
            console.error('❌ Sync failed:', error);
            this.showSyncError('Erreur: ' + error.message);
        } finally {
            this.isSyncing = false;
        }
    }

    async syncIfNeeded() {
        const lastSync = localStorage.getItem('last_product_sync');
        
        if (!lastSync) {
            console.log('🔄 First sync needed');
            await this.syncProducts();
            return;
        }

        const elapsed = Date.now() - parseInt(lastSync);
        
        if (elapsed > this.syncInterval) {
            console.log('🔄 Sync needed (last sync: ' + Math.round(elapsed / 1000 / 60) + ' min ago)');
            await this.syncProducts();
        } else {
            const remaining = Math.round((this.syncInterval - elapsed) / 1000 / 60);
            console.log(`✅ Sync not needed (next sync in ${remaining} min)`);
        }
    }

    showSyncIndicator(message) {
        toastr.info(message, 'Synchronisation', {
            timeOut: 0,
            extendedTimeOut: 0,
            closeButton: false,
            tapToDismiss: false
        });
    }

    showSyncSuccess(message) {
        toastr.clear();
        toastr.success(message, 'Synchronisation', {
            timeOut: 3000,
            closeButton: true
        });
    }

    showSyncError(message) {
        toastr.clear();
        toastr.error(message, 'Synchronisation', {
            timeOut: 5000,
            closeButton: true
        });
    }

    startAutoSync() {
        // Sync au démarrage si nécessaire
        setTimeout(() => this.syncIfNeeded(), 2000);

        // Sync périodique
        setInterval(() => this.syncIfNeeded(), this.syncInterval);

        console.log('🔄 Auto-sync started (interval: 1h)');
    }
}

// Instance globale
window.posSync = new POSSync();

// Démarrer auto-sync au chargement
window.addEventListener('load', () => {
    window.posSync.startAutoSync();
});

// Bouton manuel de sync
window.manualSync = function() {
    console.log('🔄 Manual sync triggered');
    window.posSync.syncProducts();
};
