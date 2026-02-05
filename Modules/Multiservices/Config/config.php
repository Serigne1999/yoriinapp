<?php

return [
    'name' => 'Multiservices',
    'module_version' => '1.0.0',
    
    // Opérateurs disponibles
    'operators' => [
        'wave' => [
            'name' => 'Wave',
            'color' => '#00D9A3',
            'icon' => 'fa-mobile',
            'enabled' => true,
        ],
        'orange_money' => [
            'name' => 'Orange Money',
            'color' => '#FF7900',
            'icon' => 'fa-mobile',
            'enabled' => true,
        ],
        'ria' => [
            'name' => 'RIA',
            'color' => '#C8102E',
            'icon' => 'fa-exchange',
            'enabled' => true,
        ],
        'moneygram' => [
            'name' => 'MoneyGram',
            'color' => '#E4002B',
            'icon' => 'fa-exchange',
            'enabled' => true,
        ],
        'western_union' => [
            'name' => 'Western Union',
            'color' => '#FFCC00',
            'icon' => 'fa-exchange',
            'enabled' => true,
        ],
        'autres' => [
            'name' => 'Autres',
            'color' => '#999999',
            'icon' => 'fa-exchange',
            'enabled' => true,
        ],
    ],
    
    // Types de transactions
    'transaction_types' => [
        'deposit' => 'Dépôt',
        'withdrawal' => 'Retrait',
        'transfer' => 'Transfert',
    ],
    
    // Statuts
    'statuses' => [
        'pending' => 'En attente',
        'completed' => 'Complété',
        'canceled' => 'Annulé',
        'failed' => 'Échoué',
    ],
];