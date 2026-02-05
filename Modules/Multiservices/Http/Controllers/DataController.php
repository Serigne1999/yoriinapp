<?php
namespace Modules\Multiservices\Http\Controllers;

use Illuminate\Routing\Controller;
use Menu;
use App\Utils\ModuleUtil;
class DataController extends Controller
{
    /**
     * Définit les permissions du module
     * Requis par UltimatePOS pour l'affichage dans Role Management
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'multiservices.view',
                'label' => 'Voir les transactions multiservices',
                'default' => false
            ],
            [
                'value' => 'multiservices.create',
                'label' => 'Créer des transactions multiservices',
                'default' => false
            ],
            [
                'value' => 'multiservices.update',
                'label' => 'Modifier les transactions multiservices',
                'default' => false
            ],
            [
                'value' => 'multiservices.delete',
                'label' => 'Supprimer les transactions multiservices',
                'default' => false
            ],
            [
                'value' => 'multiservices.report',
                'label' => 'Voir les rapports multiservices',
                'default' => false
            ],
            [
                'value' => 'multiservices.settings',
                'label' => 'Gérer les paramètres multiservices',
                'default' => false
            ],
        ];
    }
    /**
     * Définit les informations du module pour les packages Superadmin
     * Requis pour l'affichage dans l'interface de gestion des packages
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'multiservices',
                'label' => __('Module Multiservices'),
                'default' => false
            ]
        ];
    }

    /**
     * Ajoute le menu du module dans le sidebar
     * Requis par UltimatePOS pour l'intégration du menu
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new \App\Utils\ModuleUtil();
        
        $is_multiservices_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'multiservices');
        
        if (!$is_multiservices_enabled) {
            $subscription = \App\Models\Subscription::where('business_id', $business_id)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();
            
            if ($subscription && $subscription->package) {
                $permissions = json_decode($subscription->package->custom_permissions, true);
                $is_multiservices_enabled = isset($permissions['multiservices']) && $permissions['multiservices'];
            }
        }
        
        if (auth()->user()->can('multiservices.view') && $is_multiservices_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\Multiservices\Http\Controllers\MultiservicesController::class, 'index']),
                    'Multiservices',
                    [
                        'icon' => 'fa fa-money-bill-wave',
                        'active' => request()->segment(1) == 'multiservices',
                        'style' => config('app.env') == 'demo' ? 'background-color: #28a745;color:white' : ''
                    ]
                )->order(45);
            });
        }
    }
    /**
     * Parse les notifications du module (optionnel pour l'instant)
     */
    public function parse_notification($notification)
    {
        // Pour une future implémentation des notifications
        return [];
    }
}