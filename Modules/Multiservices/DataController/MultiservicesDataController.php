<?php

namespace Modules\Multiservices\DataController;

class MultiservicesDataController
{
    /**
     * Permissions utilisateur pour ce module
     */
    public function user_permissions()
    {
        return [
            'multiservices.view',
            'multiservices.create',
            'multiservices.update',
            'multiservices.delete',
            'multiservices.cancel',
            'multiservices.operators',
            'multiservices.accounts',
            'multiservices.commissions',
            'multiservices.reports',
            'multiservices.cash_register',
        ];
    }

    /**
     * Modifier le menu administrateur
     */
    public function modifyAdminMenu()
    {
        $business_id = request()->session()->get('user.business_id');
        $module_util = new \App\Utils\ModuleUtil();
        
        if (!$module_util->isModuleEnabled('Multiservices')) {
            return null;
        }

        return [
            [
                'name' => 'Multiservices',
                'label' => '<i class="fa fa-exchange"></i> <span>Multiservices</span>',
                'url' => action([\Modules\Multiservices\Http\Controllers\MultiservicesController::class, 'index']),
                'is_active' => request()->segment(1) == 'multiservices',
                'submenu' => [
                    [
                        'name' => 'multiservices.transactions',
                        'label' => 'Transactions',
                        'url' => action([\Modules\Multiservices\Http\Controllers\MultiservicesController::class, 'index']),
                        'is_active' => request()->segment(2) == 'transactions' || (request()->segment(2) == null && request()->segment(1) == 'multiservices'),
                    ],
                    [
                        'name' => 'multiservices.cash_register',
                        'label' => 'Caisse Multiservices',
                        'url' => action([\Modules\Multiservices\Http\Controllers\CashRegisterController::class, 'index']),
                        'is_active' => request()->segment(2) == 'caisse',
                    ],
                    [
                        'name' => 'multiservices.operators',
                        'label' => 'Opérateurs',
                        'url' => action([\Modules\Multiservices\Http\Controllers\OperatorController::class, 'index']),
                        'is_active' => request()->segment(2) == 'operators',
                    ],
                    [
                        'name' => 'multiservices.accounts',
                        'label' => 'Comptes opérateurs',
                        'url' => action([\Modules\Multiservices\Http\Controllers\OperatorAccountController::class, 'index']),
                        'is_active' => request()->segment(2) == 'accounts',
                    ],
                    [
                        'name' => 'multiservices.commissions',
                        'label' => 'Commissions',
                        'url' => action([\Modules\Multiservices\Http\Controllers\CommissionController::class, 'index']),
                        'is_active' => request()->segment(2) == 'commissions',
                    ],
                    [
                        'name' => 'multiservices.reports',
                        'label' => 'Rapports',
                        'url' => action([\Modules\Multiservices\Http\Controllers\ReportController::class, 'index']),
                        'is_active' => request()->segment(2) == 'reports',
                    ],
                ],
            ],
        ];
    }
}