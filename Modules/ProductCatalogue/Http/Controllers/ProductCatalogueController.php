<?php

namespace Modules\ProductCatalogue\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Discount;
use App\Product;
use App\SellingPriceGroup;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewCatalogueOrderNotification;
use App\User;

class ProductCatalogueController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $productUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($business_id, $location_id, $table_id = null)
    {
        $business = Business::with(['currency'])->findOrFail($business_id);
    
        $settings = json_decode($business->productcatalogue_settings, true);
        $is_show = $settings['is_show'] ?? 1;
        $is_restaurant_mode = $settings['restaurant_mode'] ?? false; // NOUVEAU
    
        $products = Product::where('business_id', $business_id)
                ->whereHas('product_locations', function ($q) use ($location_id) {
                    $q->where('product_locations.location_id', $location_id);
                })
                ->ProductForSales()
                ->with(['variations', 'variations.product_variation', 'category']);
                
        if($is_show == 0){
            $products = $products->havingRaw('
            (SELECT CASE WHEN enable_stock = 0 THEN 1 
                ELSE SUM(variation_location_details.qty_available) END
                FROM variation_location_details 
                WHERE variation_location_details.product_id = products.id) > 0');
        }
    
        $products = $products->select('products.*', DB::raw('(SELECT SUM(variation_location_details.qty_available) FROM variation_location_details WHERE variation_location_details.product_id = products.id) as stock'))
                            ->get()
                            ->groupBy('category_id');
    
        $business_location = BusinessLocation::where('business_id', $business_id)->findOrFail($location_id);
    
        // NOUVEAU - Récupérer la table si mode restaurant ET table_id fourni
        $table = null;
        if ($table_id && $is_restaurant_mode) {
            $table = DB::table('res_tables')
                ->where('id', $table_id)
                ->where('business_id', $business_id)
                ->whereNull('deleted_at')
                ->first();
        }
    
        $now = \Carbon::now()->toDateTimeString();
        $discounts = Discount::where('business_id', $business_id)
                                ->where('location_id', $location_id)
                                ->where('is_active', 1)
                                ->where('starts_at', '<=', $now)
                                ->where('ends_at', '>=', $now)
                                ->orderBy('priority', 'desc')
                                ->get();
                                
        foreach ($discounts as $key => $value) {
            $discounts[$key]->discount_amount = $this->productUtil->num_f($value->discount_amount, false, $business);
        }
    
        $categories = Category::forDropdown($business_id, 'product');
    
        return view('productcatalogue::catalogue.index')->with(compact(
            'products', 
            'business', 
            'discounts', 
            'business_location', 
            'categories',
            'table',
            'is_restaurant_mode'
        ));
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($business_id, $id)
    {
        $product = Product::with(['brand', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'product_locations', 'warranty', 'variations.variation_location_details'])->where('business_id', $business_id)
                    ->select('products.*', DB::raw('(SELECT SUM(variation_location_details.qty_available) FROM variation_location_details WHERE variation_location_details.product_id = products.id) as stock'))
                    ->findOrFail($id);
        

        $price_groups = SellingPriceGroup::where('business_id', $product->business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            $allowed_group_prices[$key] = $value;
        }

        $group_price_details = [];
        $discounts = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }

            $discounts[$variation->id] = $this->productUtil->getProductDiscount($product, $product->business_id, request()->input('location_id'), false, null, $variation->id);
        }

        $combo_variations = [];
        if ($product->type == 'combo') {
            $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $product->business_id);
        }

        $business = Business::findOrFail($business_id);

        return view('productcatalogue::catalogue.show')->with(compact(
            'product',
            'allowed_group_prices',
            'group_price_details',
            'combo_variations',
            'discounts',
            'business'
        ));
    }

    public function generateQr()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $business = Business::findOrFail($business_id);

        return view('productcatalogue::catalogue.generate_qr')
                    ->with(compact('business_locations', 'business'));
    }
    
/**
 * update product Catalogue Setting
 * @param Request $request
 */

    public function productCatalogueSetting(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $is_show = $request->post('is_show');
            $restaurant_mode = $request->has('restaurant_mode') ? true : false; // NOUVEAU
    
            $busines = Business::findOrFail($business_id);
    
            $settings = json_decode($busines->productcatalogue_settings, true);
    
            //$settings['is_show'] = $is_show;
            //$settings['restaurant_mode'] = $restaurant_mode; // NOUVEAU
            $settings = [
                'is_show' => $request->input('is_show'),
                'restaurant_mode' => $request->has('restaurant_mode') ? 1 : 0,
                'wave_payment_link' => $request->input('wave_payment_link'), // ✅ AJOUT
                'whatsapp_contact' => $request->input('whatsapp_contact'), // ✅ AJOUT
            ];
            $busines->productcatalogue_settings = json_encode($settings);
      
            $busines->update();
    
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
    
            return redirect()
                ->action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'generateQr'])
                ->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
    
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
    
            return back()->with('status', $output)->withInput();
        }
    }
        /**
     * Ajouter un produit au panier
     */
    public function addToCart(Request $request, $business_id, $location_id)
    {
        $productId = $request->input('product_id');
        $variationId = $request->input('variation_id');
        $quantity = $request->input('quantity', 1);
    
        // Récupérer le produit
        $product = Product::with(['variations'])->findOrFail($productId);
        
        // Déterminer la variation et le prix
        if ($variationId) {
            $variation = $product->variations->where('id', $variationId)->first();
            $price = $variation->sell_price_inc_tax;
            $name = $product->name . ' - ' . $variation->name;
            $sku = $variation->sub_sku;
        } else {
            $variation = $product->variations->first();
            $price = $variation->sell_price_inc_tax;
            $name = $product->name;
            $sku = $product->sku;
        }
    
        // Récupérer ou créer le panier dans la session
        $cart = session()->get("cart_{$business_id}_{$location_id}", []);
    
        // Créer une clé unique pour le produit
        $cartKey = $variationId ? "product_{$productId}_var_{$variationId}" : "product_{$productId}";
    
        // Si le produit existe déjà, augmenter la quantité
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            // Sinon, ajouter le produit
            $cart[$cartKey] = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'name' => $name,
                'sku' => $sku,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $product->image_url,
            ];
        }
    
        // Sauvegarder le panier dans la session
        session()->put("cart_{$business_id}_{$location_id}", $cart);
    
        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'cart_count' => count($cart),
            'cart' => $cart
        ]);
    }
    
    /**
     * Mettre à jour la quantité d'un produit dans le panier
     */
    public function updateCart(Request $request, $business_id, $location_id)
    {
        $cartKey = $request->input('cart_key');
        $quantity = $request->input('quantity', 1);
    
        $cart = session()->get("cart_{$business_id}_{$location_id}", []);
    
        if (isset($cart[$cartKey])) {
            if ($quantity > 0) {
                $cart[$cartKey]['quantity'] = $quantity;
            } else {
                unset($cart[$cartKey]);
            }
            session()->put("cart_{$business_id}_{$location_id}", $cart);
        }
    
        return response()->json([
            'success' => true,
            'cart_count' => count($cart),
            'cart' => $cart
        ]);
    }
    
    /**
     * Supprimer un produit du panier
     */
    public function removeFromCart(Request $request, $business_id, $location_id)
    {
        $cartKey = $request->input('cart_key');
        $cart = session()->get("cart_{$business_id}_{$location_id}", []);
    
        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put("cart_{$business_id}_{$location_id}", $cart);
        }
    
        return response()->json([
            'success' => true,
            'cart_count' => count($cart),
            'cart' => $cart
        ]);
    }
    
    /**
     * Récupérer le panier
     */
    public function getCart($business_id, $location_id)
    {
        $cart = session()->get("cart_{$business_id}_{$location_id}", []);
        
        return response()->json([
            'success' => true,
            'cart_count' => count($cart),
            'cart' => $cart
        ]);
    }
    
    /**
     * Vider le panier
     */
    public function clearCart($business_id, $location_id)
    {
        session()->forget("cart_{$business_id}_{$location_id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Panier vidé'
        ]);
    }
    /**
 * Afficher la page checkout
 */
    public function checkout($business_id, $location_id)
    {
        $business = Business::with(['currency'])->findOrFail($business_id);
        $business_location = BusinessLocation::where('business_id', $business_id)->findOrFail($location_id);
        
        // Récupérer le panier
        $cart = session()->get("cart_{$business_id}_{$location_id}", []);
        
        // NOUVEAU - Détecter mode restaurant et table
        $settings = json_decode($business->productcatalogue_settings, true);
        $is_restaurant_mode = $settings['restaurant_mode'] ?? false;
        
        $table = null;
        $table_id = request()->get('table_id') ?? session()->get('current_table_id');
        
        if ($table_id && $is_restaurant_mode) {
            $table = DB::table('res_tables')
                ->where('id', $table_id)
                ->where('business_id', $business_id)
                ->whereNull('deleted_at')
                ->first();
                
            // Stocker en session pour garder l'info
            session()->put('current_table_id', $table_id);
        }
        
        return view('productcatalogue::catalogue.checkout')->with(compact(
            'business', 
            'business_location', 
            'cart',
            'table',
            'is_restaurant_mode'
        ));
    }

/**
 * Enregistrer la commande
 */
    public function submitOrder(Request $request, $business_id, $location_id)
    {

        $business = Business::findOrFail($business_id);
        $settings = json_decode($business->productcatalogue_settings, true);
        $is_restaurant_mode = $settings['restaurant_mode'] ?? false;
        
        // Validation conditionnelle
        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
        ];
        
        // L'adresse n'est requise QUE si mode e-commerce (pas de table)
        if (!$is_restaurant_mode || !$request->has('table_id')) {
            $rules['customer_address'] = 'required|string';
        }
        
        $request->validate($rules);
    
        // Récupérer le panier
        $cart = session()->get("cart_{$business_id}_{$location_id}", []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide'
            ]);
        }
    
        // Calculer le total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    
        // Créer la commande
        $order = DB::table('catalogue_orders')->insertGetId([
            'business_id' => $business_id,
            'location_id' => $location_id,
            'table_id' => $request->table_id ?? null,
            'table_number' => $request->table_number ?? null,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address ?? ($request->table_number ? 'Table ' . $request->table_number : ''),
            'customer_notes' => $request->customer_notes,
            'items' => json_encode($cart),
            'total_amount' => $total,
            'status' => 'pending',
            'order_type' => $is_restaurant_mode && $request->has('table_id') ? 'dine_in' : 'delivery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Générer le lien Wave si le paiement est via Wave
        if ($request->payment_method == 'wave') {
            $settings = json_decode($business->productcatalogue_settings, true);
            $wave_link = $settings['wave_payment_link'] ?? '';
            
            if (!empty($wave_link)) {
                // ✅ Construire le lien Wave avec montant
                $wave_payment_url = preg_replace('/amount=\d+/', 'amount=' . intval($total), $wave_link);
                
                // Stocker le lien Wave pour l'afficher sur la page de confirmation
                session()->put('wave_payment_link', $wave_payment_url);
                
                \Log::info('Wave payment link generated', [
                    'order_id' => $order,
                    'amount' => $total,
                    'payment_method' => 'wave'
                ]);
            }
        }
        
        // Vider le panier
        session()->forget("cart_{$business_id}_{$location_id}");
        session()->forget('current_table_id');
        
        // Retourner la redirection vers la page de confirmation
        return response()->json([
            'success' => true,
            'message' => 'Commande enregistrée avec succès',
            'redirect_url' => url('/catalogue/' . $business_id . '/' . $location_id . '/order/confirmation/' . $order . ($request->payment_method == 'wave' ? '?payment=pending' : ''))
        ]);
    }

    /**
     * Page de confirmation de commande
     */
    public function orderConfirmation($business_id, $location_id, $order_id)
    {
        $business = Business::find($business_id);
        $business_location = BusinessLocation::find($location_id);
        
        $order = DB::table('catalogue_orders')
            ->where('id', $order_id)
            ->where('business_id', $business_id)
            ->first();
        
        if (!$order) {
            abort(404, 'Commande introuvable');
        }
        
        // ✅ NE PAS décoder ici, laisser en JSON string pour la vue
        
        return view('productcatalogue::catalogue.confirmation', compact('business', 'business_location', 'order'));
    }
    /**
    * Liste des commandes
    */
    public function orders(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
        if ($request->get('get_stats')) {
            $stats = [
                'total' => DB::table('catalogue_orders')->where('business_id', $business_id)->count(),
                'pending' => DB::table('catalogue_orders')->where('business_id', $business_id)->where('status', 'pending')->count(),
                'processing' => DB::table('catalogue_orders')->where('business_id', $business_id)->where('status', 'processing')->count(),
                'delivered' => DB::table('catalogue_orders')->where('business_id', $business_id)->where('status', 'delivered')->count(),
            ];
            
            return response()->json(['stats' => $stats]);
        }
        if ($request->ajax()) {
            $orders = DB::table('catalogue_orders')
                ->where('business_id', $business_id)
                ->select('*');
    
            // Filtres
            if ($request->filled('status')) {
                $orders->where('status', $request->status);
            }
            if ($request->filled('date_from')) {
                $orders->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $orders->whereDate('created_at', '<=', $request->date_to);
            }
            
    
            return datatables()->of($orders)
                ->addColumn('order_number', function ($row) {
                    return '#' . str_pad($row->id, 6, '0', STR_PAD_LEFT);
                })
                ->editColumn('total_amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->total_amount . '</span>';
                })
                ->editColumn('status', function ($row) {
                    $badges = [
                        'pending' => 'warning',
                        'paid' => 'info',
                        'processing' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $labels = [
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'processing' => 'En préparation',
                        'delivered' => 'Livré',
                        'cancelled' => 'Annulé'
                    ];
                    $badge = $badges[$row->status] ?? 'default';
                    $label = $labels[$row->status] ?? $row->status;
                    return '<span class="label label-' . $badge . '">' . $label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<a href="' . action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'orderDetails'], $row->id) . '" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> Voir</a>';
                    $html .= '<button type="button" class="btn btn-xs btn-danger delete-order" data-href="' . action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'deleteOrder'], $row->id) . '"><i class="fa fa-trash"></i></button>';
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['total_amount', 'status', 'action'])
                ->make(true);
        }
    
        return view('productcatalogue::orders.index');
    }
    
    /**
     * Détails d'une commande
     */
    public function orderDetails($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
        $order = DB::table('catalogue_orders')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->first();
    
        if (!$order) {
            abort(404);
        }
    
        return view('productcatalogue::orders.details')->with(compact('order'));
    }

    /**
     * Mettre à jour le statut
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
        try {
            DB::table('catalogue_orders')
                ->where('id', $id)
                ->where('business_id', $business_id)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);
    
            $output = [
                'success' => true,
                'msg' => __('Statut mis à jour avec succès')
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => __('Une erreur est survenue')
            ];
        }
    
        return response()->json($output);
    }
    
    /**
     * Supprimer une commande
     */
    public function deleteOrder($id)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
        try {
            DB::table('catalogue_orders')
                ->where('id', $id)
                ->where('business_id', $business_id)
                ->delete();
    
            $output = [
                'success' => true,
                'msg' => __('Commande supprimée avec succès')
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => __('Une erreur est survenue')
            ];
        }
    
        return response()->json($output);
    }
/**
* Récupérer les statistiques des commandes (pour le dashboard)
*/
    public function getOrderStats($business_id)
    {
        $stats = [
            'total' => DB::table('catalogue_orders')->where('business_id', $business_id)->count(),
            'pending' => DB::table('catalogue_orders')->where('business_id', $business_id)->where('status', 'pending')->count(),
            'processing' => DB::table('catalogue_orders')->where('business_id', $business_id)->where('status', 'processing')->count(),
            'delivered' => DB::table('catalogue_orders')->where('business_id', $business_id)->where('status', 'delivered')->count(),
        ];
        
        return $stats;
    }
    /**
 * Page de gestion des QR codes des tables restaurant
 */
    public function restaurantTables()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
        return view('productcatalogue::tables.index');
    }
    
    /**
     * Liste des tables restaurant (DataTables)
     */
    public function getResTables(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
        if ($request->ajax()) {
            $tables = DB::table('res_tables')
                ->leftJoin('business_locations', 'res_tables.location_id', '=', 'business_locations.id')
                ->where('res_tables.business_id', $business_id)
                ->whereNull('res_tables.deleted_at')
                ->select('res_tables.*', 'business_locations.name as location_name');
    
            return datatables()->of($tables)
                ->addColumn('qr_preview', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-primary view-qr" 
                            data-table-id="' . $row->id . '" 
                            data-table-name="' . $row->name . '"
                            data-business-id="' . $row->business_id . '"
                            data-location-id="' . $row->location_id . '">
                            <i class="fa fa-qrcode"></i> Voir QR
                        </button>';
                })
                ->addColumn('action', function ($row) {
                    $catalogueUrl = url('/catalogue/' . $row->business_id . '/' . $row->location_id . '/' . $row->id);
                    return '<a href="' . $catalogueUrl . '" target="_blank" class="btn btn-sm btn-success">
                            <i class="fa fa-external-link"></i> Tester
                        </a>';
                })
                ->rawColumns(['qr_preview', 'action'])
                ->make(true);
        }
    }
    
    /**
     * Imprimer tous les QR codes des tables
     */
    public function printAllTableQR()
    {
        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
            abort(403, 'Unauthorized action.');
        }
    
        $business = Business::with(['currency'])->findOrFail($business_id);
        
        $tables = DB::table('res_tables')
            ->leftJoin('business_locations', 'res_tables.location_id', '=', 'business_locations.id')
            ->where('res_tables.business_id', $business_id)
            ->whereNull('res_tables.deleted_at')
            ->select('res_tables.*', 'business_locations.name as location_name')
            ->get();
    
        return view('productcatalogue::tables.print_all')->with(compact('tables', 'business'));
    }
    /**
 * Préparer une commande pour le POS
 */
    public function prepareOrderForPOS($order_id)
    {
        DB::beginTransaction();
        
        try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = auth()->user()->id;
            
            if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'productcatalogue_module'))) {
                abort(403, 'Unauthorized action.');
            }
    
            // Récupérer la commande
            $order = DB::table('catalogue_orders')
                ->where('id', $order_id)
                ->where('business_id', $business_id)
                ->first();
    
            if (!$order) {
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'Commande introuvable']);
            }
    
            $items = json_decode($order->items, true);
            
            if (!$items || count($items) == 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'Aucun produit dans la commande']);
            }
            
            // Créer/récupérer le contact
            $contact_id = null;
            if (!empty($order->customer_phone)) {
                $contact = DB::table('contacts')
                    ->where('business_id', $business_id)
                    ->where('mobile', $order->customer_phone)
                    ->first();
                
                if (!$contact) {
                    $contact_id = DB::table('contacts')->insertGetId([
                        'business_id' => $business_id,
                        'type' => 'customer',
                        'supplier_business_name' => null,
                        'name' => $order->customer_name ?? 'Client',
                        'mobile' => $order->customer_phone,
                        'credit_limit' => null,
                        'created_by' => $user_id,
                        'is_default' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $contact_id = $contact->id;
                }
            }
            
            // Récupérer le location_id
            $location_id = request()->session()->get('user.business_location_id');
    
            // Si pas en session, récupérer depuis la commande ou la première location du business
            if (!$location_id) {
                // Option 1 : Depuis la commande (si elle a un location_id)
                if (!empty($order->location_id)) {
                    $location_id = $order->location_id;
                } 
                // Option 2 : Prendre la première location active du business
                else {
                    $location = DB::table('business_locations')
                        ->where('business_id', $business_id)
                        ->where('is_active', 1)
                        ->orderBy('id', 'asc')
                        ->first();
                    
                    if ($location) {
                        $location_id = $location->id;
                    }
                }
            }
    
            if (!$location_id) {
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'Aucune location disponible pour ce business']);
            }
            
            // ✅ LOGS DE VÉRIFICATION
            \Log::info('Tentative création transaction', [
                'business_id' => $business_id,
                'location_id' => $location_id,
                'contact_id' => $contact_id,
                'created_by' => $user_id,
            ]);
    
            // Vérifier que les IDs existent vraiment
            $business_exists = DB::table('business')->where('id', $business_id)->exists();
            $location_exists = DB::table('business_locations')->where('id', $location_id)->exists();
            $contact_exists = $contact_id ? DB::table('contacts')->where('id', $contact_id)->exists() : true;
            $user_exists = DB::table('users')->where('id', $user_id)->exists();
    
            \Log::info('Vérification existence', [
                'business_exists' => $business_exists,
                'location_exists' => $location_exists,
                'contact_exists' => $contact_exists,
                'user_exists' => $user_exists,
            ]);
    
            if (!$business_exists || !$location_exists || !$user_exists || !$contact_exists) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'msg' => 'Données invalides : business='.$business_exists.', location='.$location_exists.', user='.$user_exists.', contact='.$contact_exists
                ]);
            }
            // 🪑 Récupérer la table restaurant si présente
            $res_table_id = null;
            
            if (!empty($order->table_id)) {
                // Cas le plus courant : table_id stocké dans catalogue_orders
                $res_table_id = $order->table_id;
            } elseif (!empty($order->res_table_id)) {
                // Cas fallback si déjà stocké sous ce nom
                $res_table_id = $order->res_table_id;
            }
            
            // Vérifier que la table existe réellement (sécurité)
            if ($res_table_id) {
                $table_exists = DB::table('res_tables')
                    ->where('id', $res_table_id)
                    ->where('business_id', $business_id)
                    ->exists();
            
                if (!$table_exists) {
                    $res_table_id = null;
                }
            }

            
            // VERSION MINIMALE - Créer le devis
            $transaction_id = DB::table('transactions')->insertGetId([
                'business_id' => $business_id,
                'location_id' => $location_id,
                'type' => 'sell',
                'status' => 'draft',
                'res_table_id' => $res_table_id,
                'contact_id' => $contact_id,
                'transaction_date' => now(),
                'created_by' => $user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            \Log::info('Transaction créée', ['transaction_id' => $transaction_id]);
            
            if (!$transaction_id) {
                \Log::error('ERREUR : transaction_id est null !');
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'Impossible de créer la transaction']);
            }
            
            // DEBUG : Afficher les items
            \Log::info('Items à insérer', ['items' => $items, 'transaction_id' => $transaction_id]);
            
            // Ajouter les produits
            $total = 0;
            
            foreach ($items as $item) {
                \Log::info('Traitement item', ['item' => $item]);
                
                $variation_id = $item['variation_id'] ?? null;
                
                // Si variation_id est null, récupérer la première variation
                if (!$variation_id && !empty($item['product_id'])) {
                    $default_variation = DB::table('variations')
                        ->where('product_id', $item['product_id'])
                        ->first();
                    
                    if ($default_variation) {
                        $variation_id = $default_variation->id;
                    }
                }
                
                if (!$variation_id) {
                    \Log::warning('Variation non trouvée', ['item' => $item]);
                    continue;
                }
                
                $quantity = $item['quantity'] ?? 1;
                $unit_price = $item['price'] ?? 0;
                $line_total = $quantity * $unit_price;
                $total += $line_total;
                
                DB::table('transaction_sell_lines')->insert([
                    'transaction_id' => $transaction_id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $variation_id,
                    'quantity' => $quantity,
                    'quantity_returned' => 0,
                    'unit_price_before_discount' => $unit_price,
                    'unit_price' => $unit_price,
                    'line_discount_type' => 'fixed',
                    'line_discount_amount' => 0,
                    'unit_price_inc_tax' => $unit_price,
                    'item_tax' => 0,
                    'tax_id' => null,
                    'discount_id' => null,
                    'lot_no_line_id' => null,
                    'sell_line_note' => null,
                    'sub_unit_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                \Log::info('Ligne produit insérée', ['transaction_id' => $transaction_id, 'variation_id' => $variation_id]);
            }
    
            // Mettre à jour le total
            DB::table('transactions')
                ->where('id', $transaction_id)
                ->update([
                    'final_total' => $total,
                    'total_before_tax' => $total,
                ]);
            
            \Log::info('Total mis à jour', ['transaction_id' => $transaction_id, 'total' => $total]);
    
            // Marquer la commande comme traitée
            DB::table('catalogue_orders')
                ->where('id', $order_id)
                ->update(['status' => 'processing', 'updated_at' => now()]);
    
            DB::commit();
    
            // URL vers le devis
            $pos_url = action([\App\Http\Controllers\SellPosController::class, 'edit'], [$transaction_id]);
    
            return response()->json(['success' => true, 'pos_url' => $pos_url]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur prepareOrderForPOS', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
    /**
     * Vérifier les nouvelles commandes (pour les notifications)
     */
    public function checkNewOrders(Request $request)
    {
        return;
        $business_id = session('business.id');
        $last_order_id = $request->input('last_order_id', 0);
        
        $new_orders = DB::table('catalogue_orders')
            ->where('business_id', $business_id)
            ->where('id', '>', $last_order_id)
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get(['id', 'customer_name', 'total', 'created_at']);
        
        $latest_order_id = DB::table('catalogue_orders')
            ->where('business_id', $business_id)
            ->max('id') ?? 0;
        
        return response()->json([
            'new_orders' => $new_orders,
            'latest_order_id' => $latest_order_id,
            'count' => count($new_orders)
        ]);
    }
}
