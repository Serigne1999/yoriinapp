// POS Offline App
class POSOfflineApp {
    constructor() {
        this.cart = [];
        this.products = [];
        this.filteredProducts = [];
        this.selectedLocation = null;
    }

    async init() {
        console.log('🚀 Initializing POS Offline...');
        
        // Charger les produits du cache
        await this.loadProducts();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Update online/offline indicator
        this.updateOnlineStatus();
    }

    async loadProducts() {
        try {
            $('#products-loading').show();
            $('#products-grid').hide();
            $('#no-products').hide();

            // Récupérer produits depuis IndexedDB
            this.products = await window.posDB.getProducts();
            
            if (this.products.length === 0) {
                console.warn('⚠️ No products in cache');
                $('#products-loading').hide();
                $('#no-products').show();
                return;
            }

            console.log(`✅ Loaded ${this.products.length} products from cache`);
            
            this.filteredProducts = this.products;
            this.renderProducts();
            
            $('#products-loading').hide();
            $('#products-grid').show();
            
        } catch (error) {
            console.error('❌ Error loading products:', error);
            $('#products-loading').hide();
            $('#no-products').show();
        }
    }

    renderProducts() {
        const grid = $('#products-grid');
        grid.empty();

        if (this.filteredProducts.length === 0) {
            grid.html('<div class="col-md-12 text-center text-muted" style="padding: 50px;"><p>Aucun produit trouvé</p></div>');
            return;
        }

        this.filteredProducts.forEach(product => {
            const price = parseFloat(product.sell_price_inc_tax || product.default_sell_price || 0);
            const stock = product.enable_stock ? (product.qty_available || 0) : '∞';
            
            const card = `
                <div class="col-xs-6 col-sm-4 col-md-3" style="margin-bottom: 15px;">
                    <div class="product-card" onclick="posApp.addToCart(${product.id})" 
                         style="cursor: pointer; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background: white; transition: all 0.2s; height: 100%;">
                        <div style="text-align: center;">
                            ${product.image_url ? 
                                `<img src="${product.image_url}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 10px;">` :
                                `<div style="width: 100%; height: 120px; background: #f0f0f0; border-radius: 4px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>`
                            }
                            <h5 style="margin: 5px 0; font-size: 14px; font-weight: bold; min-height: 40px;">${product.name}</h5>
                            <p style="margin: 5px 0; font-size: 12px; color: #666;">${product.sku || ''}</p>
                            <div style="margin-top: 10px;">
                                <span style="font-size: 18px; font-weight: bold; color: #00a65a;">${price.toFixed(0)} FCFA</span>
                            </div>
                            <div style="margin-top: 5px;">
                                <span class="label ${stock === '∞' || stock > 0 ? 'label-success' : 'label-danger'}" style="font-size: 11px;">
                                    Stock: ${stock}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            grid.append(card);
        });

        // Hover effect
        $('.product-card').hover(
            function() { $(this).css({'box-shadow': '0 4px 12px rgba(0,0,0,0.15)', 'transform': 'translateY(-2px)'}); },
            function() { $(this).css({'box-shadow': 'none', 'transform': 'none'}); }
        );
    }

    addToCart(productId) {
        const product = this.products.find(p => p.id === productId);
        if (!product) return;

        const existingItem = this.cart.find(item => item.product.id === productId);
        
        if (existingItem) {
            existingItem.quantity++;
        } else {
            this.cart.push({
                product: product,
                quantity: 1,
                price: parseFloat(product.sell_price_inc_tax || product.default_sell_price || 0)
            });
        }

        this.renderCart();
        
        // Feedback sonore/visuel
        toastr.success(`${product.name} ajouté au panier`, '', {timeOut: 1000});
    }

    renderCart() {
        const cartContainer = $('#cart-items');
        const emptyCart = $('#empty-cart');
        
        if (this.cart.length === 0) {
            emptyCart.show();
            cartContainer.find('.cart-item').remove();
            $('#btn-finalize').prop('disabled', true);
            $('#cart-count').text('0');
            $('#cart-total').text('0 FCFA');
            return;
        }

        emptyCart.hide();
        cartContainer.find('.cart-item').remove();
        
        let total = 0;
        let totalItems = 0;

        this.cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            totalItems += item.quantity;

            const cartItem = `
                <div class="cart-item" style="border-bottom: 1px solid #eee; padding: 10px 0; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong style="font-size: 13px;">${item.product.name}</strong>
                            <p style="margin: 2px 0; font-size: 12px; color: #666;">${item.price.toFixed(0)} FCFA</p>
                        </div>
                        <button onclick="posApp.removeFromCart(${index})" class="btn btn-xs btn-danger">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div style="display: flex; align-items: center; margin-top: 5px;">
                        <button onclick="posApp.updateQuantity(${index}, -1)" class="btn btn-sm btn-default">
                            <i class="fa fa-minus"></i>
                        </button>
                        <input type="number" value="${item.quantity}" 
                               onchange="posApp.setQuantity(${index}, this.value)"
                               style="width: 60px; text-align: center; margin: 0 5px; padding: 5px;" 
                               class="form-control input-sm" min="1">
                        <button onclick="posApp.updateQuantity(${index}, 1)" class="btn btn-sm btn-default">
                            <i class="fa fa-plus"></i>
                        </button>
                        <span style="margin-left: auto; font-weight: bold;">${itemTotal.toFixed(0)} FCFA</span>
                    </div>
                </div>
            `;
            
            cartContainer.append(cartItem);
        });

        $('#cart-count').text(totalItems);
        $('#cart-total').text(total.toFixed(0) + ' FCFA');
        $('#btn-finalize').prop('disabled', false);
    }

    updateQuantity(index, delta) {
        if (this.cart[index]) {
            this.cart[index].quantity += delta;
            if (this.cart[index].quantity <= 0) {
                this.cart.splice(index, 1);
            }
            this.renderCart();
        }
    }

    setQuantity(index, value) {
        const qty = parseInt(value);
        if (qty > 0 && this.cart[index]) {
            this.cart[index].quantity = qty;
            this.renderCart();
        }
    }

    removeFromCart(index) {
        this.cart.splice(index, 1);
        this.renderCart();
    }

    clearCart() {
        if (this.cart.length === 0) return;
        
        if (confirm('Vider le panier ?')) {
            this.cart = [];
            this.renderCart();
            toastr.info('Panier vidé');
        }
    }

    async finalizeSale() {
        if (this.cart.length === 0) return;

        // Calculer total
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Préparer données vente
        const saleData = {
            products: this.cart.map(item => ({
                product_id: item.product.id,
                variation_id: item.product.variation_id || item.product.id,
                quantity: item.quantity,
                unit_price: item.price,
                line_total: item.price * item.quantity
            })),
            final_total: total,
            location_id: this.selectedLocation || 1,
            contact_id: null,
            transaction_date: new Date().toISOString(),
            payment_status: 'paid',
            payment_method: 'cash',
            created_offline: true,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        };

        try {
            // Sauvegarder en local
            await window.posDB.savePendingSale(saleData);
            
            toastr.success('✅ Vente enregistrée localement', '', {timeOut: 3000});
            
            // Vider panier
            this.cart = [];
            this.renderCart();
            
            // Afficher recap
            this.showSaleRecap(total);
            
        } catch (error) {
            console.error('❌ Error saving sale:', error);
            toastr.error('Erreur lors de l\'enregistrement');
        }
    }

    showSaleRecap(total) {
        const html = `
            <div class="alert alert-success" style="margin: 15px;">
                <h4><i class="fa fa-check-circle"></i> Vente finalisée !</h4>
                <p style="font-size: 24px; margin: 10px 0;"><strong>${total.toFixed(0)} FCFA</strong></p>
                <p>La vente sera synchronisée automatiquement quand vous serez en ligne.</p>
            </div>
        `;
        
        $('#sync-status').html(html).show();
        setTimeout(() => $('#sync-status').fadeOut(), 5000);
    }

    setupEventListeners() {
        // Recherche produits
        $('#product-search').on('input', (e) => {
            this.searchProducts(e.target.value);
        });

        // Filtre location
        $('#location-filter').on('change', (e) => {
            this.selectedLocation = e.target.value;
            this.filterByLocation();
        });

        // Boutons panier
        $('#btn-clear-cart').on('click', () => this.clearCart());
        $('#btn-finalize').on('click', () => this.finalizeSale());

        // Online/offline
        window.addEventListener('online', () => this.updateOnlineStatus());
        window.addEventListener('offline', () => this.updateOnlineStatus());
    }

    searchProducts(query) {
        if (!query) {
            this.filteredProducts = this.products;
        } else {
            query = query.toLowerCase();
            this.filteredProducts = this.products.filter(p => 
                p.name.toLowerCase().includes(query) ||
                (p.sku && p.sku.toLowerCase().includes(query))
            );
        }
        this.renderProducts();
    }

    filterByLocation() {
        // TODO: Filter by location if needed
        this.renderProducts();
    }

    updateOnlineStatus() {
        const indicator = $('#offline-indicator');
        if (navigator.onLine) {
            indicator.hide();
        } else {
            indicator.show();
        }
    }
}

// Initialize app
let posApp;
$(document).ready(async function() {
    posApp = new POSOfflineApp();
    await posApp.init();
});
