<?php

use Modules\Multiservices\Http\Controllers\TransactionTypeController;
use Illuminate\Support\Facades\Route;
use Modules\Multiservices\Http\Controllers\MultiservicesController;
use Modules\Multiservices\Http\Controllers\CommissionController;
use Modules\Multiservices\Http\Controllers\ReportController;
use Modules\Multiservices\Http\Controllers\OperatorController;
use Modules\Multiservices\Http\Controllers\OperatorAccountController;
use Modules\Multiservices\Http\Controllers\CashRegisterController;

// Transactions
Route::get('/transactions', [MultiservicesController::class, 'index'])->name('multiservices.index');
Route::get('/create', [MultiservicesController::class, 'create'])->name('multiservices.create');
Route::get('/get-accounts/{operator}', [MultiservicesController::class, 'getAccountsByOperator'])->name('multiservices.get-accounts');
Route::post('/', [MultiservicesController::class, 'store'])->name('multiservices.store');
Route::get('/{id}', [MultiservicesController::class, 'show'])->where('id', '[0-9]+')->name('multiservices.show');
Route::get('/{id}/edit', [MultiservicesController::class, 'edit'])->where('id', '[0-9]+')->name('multiservices.edit');
Route::put('/{id}', [MultiservicesController::class, 'update'])->where('id', '[0-9]+')->name('multiservices.update');
Route::delete('/{id}', [MultiservicesController::class, 'destroy'])->where('id', '[0-9]+')->name('multiservices.destroy');

// Actions sur transactions
Route::post('/{id}/complete', [MultiservicesController::class, 'complete'])->where('id', '[0-9]+')->name('multiservices.complete');
Route::post('/{id}/cancel', [MultiservicesController::class, 'cancel'])->where('id', '[0-9]+')->name('multiservices.cancel');

// Calcul des frais (AJAX)
Route::post('/calculate-fees', [MultiservicesController::class, 'calculateFees'])->name('multiservices.calculate-fees');

// Commissions
Route::get('/commissions', [CommissionController::class, 'index'])->name('multiservices.commissions.index');
Route::post('/commissions', [CommissionController::class, 'store'])->name('multiservices.commissions.store');
Route::put('/commissions/{id}', [CommissionController::class, 'update'])->where('id', '[0-9]+')->name('multiservices.commissions.update');
Route::delete('/commissions/{id}', [CommissionController::class, 'destroy'])->where('id', '[0-9]+')->name('multiservices.commissions.destroy');
Route::post('/commissions/{id}/toggle', [CommissionController::class, 'toggleActive'])->where('id', '[0-9]+')->name('multiservices.commissions.toggle');

// Rapports
Route::get('/reports', [ReportController::class, 'index'])->name('multiservices.reports.index');
Route::get('/reports/cash', [ReportController::class, 'cashReport'])->name('multiservices.reports.cash');
Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('multiservices.reports.export-pdf');
Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('multiservices.reports.export-excel');

// Gestion des opérateurs
Route::get('/operators', [OperatorController::class, 'index'])->name('multiservices.operators.index');
Route::post('/operators', [OperatorController::class, 'store'])->name('multiservices.operators.store');
Route::get('/operators/{id}', [OperatorController::class, 'get'])->name('multiservices.operators.get');
Route::put('/operators/{id}', [OperatorController::class, 'update'])->name('multiservices.operators.update');
Route::post('/operators/{id}/toggle', [OperatorController::class, 'toggle'])->name('multiservices.operators.toggle');
Route::delete('/operators/{id}', [OperatorController::class, 'destroy'])->name('multiservices.operators.destroy');

// Comptes opérateurs
Route::get('/accounts', [OperatorAccountController::class, 'index'])->name('multiservices.accounts.index');
Route::get('/accounts/create', [OperatorAccountController::class, 'create'])->name('multiservices.accounts.create');
Route::post('/accounts', [OperatorAccountController::class, 'store'])->name('multiservices.accounts.store');
Route::get('/accounts/{id}/edit', [OperatorAccountController::class, 'edit'])->name('multiservices.accounts.edit');
Route::put('/accounts/{id}', [OperatorAccountController::class, 'update'])->name('multiservices.accounts.update');
Route::delete('/accounts/{id}', [OperatorAccountController::class, 'destroy'])->name('multiservices.accounts.destroy');
Route::post('/accounts/{id}/fund', [OperatorAccountController::class, 'fund'])->name('multiservices.accounts.fund');
Route::get('/accounts/{id}/history', [OperatorAccountController::class, 'history'])->name('multiservices.accounts.history');
Route::get('/accounts/reports', [OperatorAccountController::class, 'index'])->name('multiservices.accounts.reports');
// Ajustement solde compte opérateur
Route::get('/operator-accounts/{id}/adjust', [OperatorAccountController::class, 'showAdjustForm'])->name('operator-accounts.adjust');
Route::post('/operator-accounts/{id}/adjust', [OperatorAccountController::class, 'processAdjustment'])->name('operator-accounts.adjust.process');
// Types de transactions
Route::get('/transaction-types', [TransactionTypeController::class, 'index'])->name('multiservices.transaction-types.index');
Route::post('/transaction-types', [TransactionTypeController::class, 'store'])->name('multiservices.transaction-types.store');
Route::get('/transaction-types/{id}', [TransactionTypeController::class, 'get'])->name('multiservices.transaction-types.get');
Route::put('/transaction-types/{id}', [TransactionTypeController::class, 'update'])->name('multiservices.transaction-types.update');
Route::post('/transaction-types/{id}/toggle', [TransactionTypeController::class, 'toggle'])->name('multiservices.transaction-types.toggle');
Route::delete('/transaction-types/{id}', [TransactionTypeController::class, 'destroy'])->name('multiservices.transaction-types.destroy');

// CAISSE MULTISERVICES SÉPARÉE
Route::prefix('caisse')->name('cash-register.')->group(function() {
    // Liste et gestion
    Route::get('/', [CashRegisterController::class, 'index'])->name('index');
    Route::get('/ouvrir', [CashRegisterController::class, 'create'])->name('create');
    Route::post('/ouvrir', [CashRegisterController::class, 'store'])->name('store');
    Route::get('/{id}', [CashRegisterController::class, 'show'])->name('show');
    
    // Opérations sur caisse
    Route::post('/{id}/alimenter', [CashRegisterController::class, 'fund'])->name('fund');
    Route::get('/{id}/prelever', [CashRegisterController::class, 'showExpenseForm'])->name('expense');
    Route::post('/{id}/prelever', [CashRegisterController::class, 'processExpense'])->name('expense.process');
    Route::post('/{id}/fermer', [CashRegisterController::class, 'close'])->name('close');
    
    // Annulation/Suppression mouvements
    Route::post('/{id}/cancel-funding/{transactionId}', [CashRegisterController::class, 'cancelFunding'])->name('cancel-funding');
        
    Route::post('/{id}/cancel-expense/{transactionId}', [CashRegisterController::class, 'cancelExpense'])->name('cancel-expense');
        
    Route::delete('/{id}/delete-expense/{transactionId}', [CashRegisterController::class, 'deleteExpense'])->name('delete-expense');
});