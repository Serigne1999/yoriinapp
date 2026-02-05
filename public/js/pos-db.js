class POSDatabase {
    constructor() {
        this.dbName = 'YoriinAppPOS';
        this.dbVersion = 1;
        this.db = null;
    }

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => {
                console.error('❌ DB Error:', request.error);
                reject(request.error);
            };
            
            request.onsuccess = () => {
                this.db = request.result;
                console.log('✅ DB Opened');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                console.log('🔧 DB Upgrade needed');

                // Store produits
                if (!db.objectStoreNames.contains('products')) {
                    const productStore = db.createObjectStore('products', { keyPath: 'id' });
                    productStore.createIndex('sku', 'sku', { unique: false });
                    productStore.createIndex('name', 'name', { unique: false });
                    console.log('✅ Products store created');
                }

                // Store ventes en attente
                if (!db.objectStoreNames.contains('pending_sales')) {
                    const salesStore = db.createObjectStore('pending_sales', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    salesStore.createIndex('timestamp', 'timestamp', { unique: false });
                    salesStore.createIndex('synced', 'synced', { unique: false });
                    console.log('✅ Pending sales store created');
                }

                // Store panier actuel
                if (!db.objectStoreNames.contains('cart')) {
                    db.createObjectStore('cart', { keyPath: 'product_id' });
                    console.log('✅ Cart store created');
                }
            };
        });
    }

    // === PRODUITS ===
    async syncProducts(products) {
        const tx = this.db.transaction('products', 'readwrite');
        const store = tx.objectStore('products');
        
        // Vider store
        await store.clear();
        
        let successCount = 0;
        
        // Ajouter tous les produits
        for (const product of products) {
            try {
                // S'assurer que l'ID existe
                if (!product.id && product.product_id) {
                    product.id = product.product_id;
                } else if (!product.id) {
                    console.warn('Product sans ID ignoré:', product);
                    continue;
                }
                
                await store.add(product);
                successCount++;
            } catch (error) {
                console.error('Erreur ajout produit:', product, error);
            }
        }
        
        localStorage.setItem('last_product_sync', Date.now());
        localStorage.setItem('product_count', successCount);
        
        console.log(`✅ ${successCount}/${products.length} products synced to IndexedDB`);
        return successCount;
    }

    async getProducts() {
        const tx = this.db.transaction('products', 'readonly');
        const store = tx.objectStore('products');
        return this.getAll(store);
    }

    async searchProducts(query) {
        const products = await this.getProducts();
        query = query.toLowerCase();
        
        return products.filter(p => 
            p.name.toLowerCase().includes(query) ||
            (p.sku && p.sku.toLowerCase().includes(query))
        );
    }

    // === PANIER ===
    async addToCart(product, quantity = 1) {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');
        
        const existing = await store.get(product.id);
        
        if (existing) {
            existing.quantity += quantity;
            await store.put(existing);
        } else {
            await store.add({
                product_id: product.id,
                name: product.name,
                price: parseFloat(product.sell_price_inc_tax || product.default_sell_price || 0),
                quantity: quantity,
                product: product
            });
        }
        
        console.log(`✅ Added ${product.name} x${quantity} to cart`);
        return this.getCart();
    }

    async getCart() {
        const tx = this.db.transaction('cart', 'readonly');
        const store = tx.objectStore('cart');
        return this.getAll(store);
    }

    async updateCartQuantity(productId, quantity) {
        const tx = this.db.transaction('cart', 'readwrite');
        const store = tx.objectStore('cart');
        const item = await store.get(productId);
        
        if (item) {
            if (quantity <= 0) {
                await store.delete(productId);
                console.log(`🗑️ Removed product ${productId} from cart`);
            } else {
                item.quantity = quantity;
                await store.put(item);
                console.log(`✅ Updated quantity for product ${productId}: ${quantity}`);
            }
        }
        
        return this.getCart();
    }

    async clearCart() {
        const tx = this.db.transaction('cart', 'readwrite');
        await tx.objectStore('cart').clear();
        console.log('🗑️ Cart cleared');
    }

    // === VENTES ===
    async savePendingSale(saleData) {
        const tx = this.db.transaction('pending_sales', 'readwrite');
        const store = tx.objectStore('pending_sales');
        
        const sale = {
            ...saleData,
            timestamp: Date.now(),
            synced: false,
            local_id: 'LOCAL_' + Date.now()
        };
        
        const id = await this.add(store, sale);
        console.log('💾 Sale saved locally:', id);
        
        // Déclencher Background Sync
        if ('serviceWorker' in navigator && 'sync' in navigator.serviceWorker) {
            try {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('sync-sales');
                console.log('🔄 Background sync registered');
            } catch (err) {
                console.error('❌ Background sync failed:', err);
            }
        }
        
        return id;
    }

    async getPendingSales() {
        const tx = this.db.transaction('pending_sales', 'readonly');
        const store = tx.objectStore('pending_sales');
        const all = await this.getAll(store);
        return all.filter(s => !s.synced);
    }

    async getPendingSalesCount() {
        const sales = await this.getPendingSales();
        return sales.length;
    }

    // Helpers
    getAll(store) {
        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    add(store, data) {
        return new Promise((resolve, reject) => {
            const request = store.add(data);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
}

// Instance globale
window.posDB = new POSDatabase();

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', async function() {
    try {
        await window.posDB.init();
        console.log('✅ POS Database ready');
        
        // Afficher stats
        const productCount = localStorage.getItem('product_count') || 0;
        const lastSync = localStorage.getItem('last_product_sync');
        
        if (lastSync) {
            const syncDate = new Date(parseInt(lastSync));
            console.log(`📊 ${productCount} products in cache (synced: ${syncDate.toLocaleString()})`);
        }
        
        // Afficher ventes en attente
        const pendingCount = await window.posDB.getPendingSalesCount();
        if (pendingCount > 0) {
            console.log(`⏳ ${pendingCount} sales pending sync`);
        }
    } catch (err) {
        console.error('❌ Failed to init POS DB:', err);
    }
});
