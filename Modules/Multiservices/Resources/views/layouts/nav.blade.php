<!-- Desktop : Menu horizontal -->
<div class="tw-mt-6 tw-mb-6 tw-hidden md:tw-flex tw-justify-center">
    <div class="tw-bg-white tw-rounded-full tw-shadow-sm tw-p-1 tw-inline-flex tw-gap-1">
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\MultiservicesController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == null || !in_array(request()->segment(2), ['transaction-types', 'operators', 'accounts', 'caisse', 'commissions', 'reports']) ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Transactions
        </a>

        @can('multiservices.settings')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\TransactionTypeController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'transaction-types' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Types de Transactions
        </a>
        @endcan

        @can('multiservices.settings')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\OperatorController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'operators' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Opérateurs
        </a>
        @endcan

        @can('multiservices.view')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\OperatorAccountController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'accounts' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Comptes
        </a>
        @endcan

        @can('multiservices.view')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\CashRegisterController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'caisse' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Caisse
        </a>
        @endcan

        @can('multiservices.settings')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\CommissionController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'commissions' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Commissions
        </a>
        @endcan

        @can('multiservices.report')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\ReportController::class, 'index']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'reports' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Rapports
        </a>
        @endcan
        @can('multiservices.report')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\ReportController::class, 'cashReport']) }}" 
           class="tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors tw-whitespace-nowrap {{ request()->segment(2) == 'reports' && request()->segment(3) == 'cash' ? 'tw-bg-gray-100 tw-text-gray-900' : 'tw-text-gray-600 hover:tw-bg-gray-50' }}">
            &nbsp;Rapport de Caisse
        </a>
        @endcan
    </div>
</div>

<!-- Mobile : Titre + Hamburger + Dropdown -->
<div class="tw-mt-6 tw-mb-6 tw-block md:tw-hidden">
    <div class="tw-bg-white tw-rounded-full tw-shadow-sm tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between">
        <div class="tw-flex tw-items-center tw-gap-2">
            <i class="fa fa-money-bill-wave tw-text-gray-600"></i>
            <span class="tw-font-medium tw-text-gray-800">Multiservices</span>
        </div>
        
        <button type="button" class="tw-p-2 tw-text-gray-600 hover:tw-text-gray-900" id="mobile-menu-btn">
            <i class="fa fa-bars tw-text-xl"></i>
        </button>
    </div>
    
    <!-- Dropdown Menu (caché par défaut) -->
    <div id="mobile-menu-dropdown" class="tw-hidden tw-mt-2 tw-bg-white tw-rounded-lg tw-shadow-lg tw-overflow-hidden">
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\MultiservicesController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 {{ request()->segment(2) == null ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-list tw-mr-2"></i> Transactions
        </a>

        @can('multiservices.settings')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\TransactionTypeController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 {{ request()->segment(2) == 'transaction-types' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-tags tw-mr-2"></i> Types de Transactions
        </a>
        @endcan

        @can('multiservices.settings')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\OperatorController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 {{ request()->segment(2) == 'operators' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-building tw-mr-2"></i> Opérateurs
        </a>
        @endcan

        @can('multiservices.view')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\OperatorAccountController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 {{ request()->segment(2) == 'accounts' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-wallet tw-mr-2"></i> Comptes
        </a>
        @endcan

        @can('multiservices.view')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\CashRegisterController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 {{ request()->segment(2) == 'caisse' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-cash-register tw-mr-2"></i> Caisse
        </a>
        @endcan

        @can('multiservices.settings')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\CommissionController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 tw-border-b tw-border-gray-100 {{ request()->segment(2) == 'commissions' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-percent tw-mr-2"></i> Commissions
        </a>
        @endcan

        @can('multiservices.report')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\ReportController::class, 'index']) }}" 
           class="tw-block tw-px-4 tw-py-3 {{ request()->segment(2) == 'reports' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-chart-bar tw-mr-2"></i> Rapports
        </a>
        @endcan
        
        @can('multiservices.report')
        <a href="{{ action([\Modules\Multiservices\Http\Controllers\ReportController::class, 'cashReport']) }}" 
           class="tw-block tw-px-4 tw-py-3 {{ request()->segment(2) == 'reports' && request()->segment(3) == 'cash' ? 'tw-bg-blue-50 tw-text-blue-600' : 'tw-text-gray-700' }} hover:tw-bg-gray-50">
            <i class="fa fa-cash-register tw-mr-2"></i> Rapport de Caisse
        </a>
        @endcan
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const menuDropdown = document.getElementById('mobile-menu-dropdown');
    
    if (menuBtn && menuDropdown) {
        menuBtn.addEventListener('click', function() {
            menuDropdown.classList.toggle('tw-hidden');
        });
        
        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(event) {
            if (!menuBtn.contains(event.target) && !menuDropdown.contains(event.target)) {
                menuDropdown.classList.add('tw-hidden');
            }
        });
    }
});
</script>