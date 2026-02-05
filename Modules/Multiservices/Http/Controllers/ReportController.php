<?php

namespace Modules\Multiservices\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Multiservices\Entities\MultiserviceTransaction;
use Modules\Multiservices\Entities\MultiservicesCashRegister;
use Modules\Multiservices\Entities\MultiservicesCashTransaction;
use Carbon\Carbon;
use DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('multiservices.report')) {
            abort(403, 'Accès non autorisé');
        }

        $businessId = auth()->user()->business_id;

        // Dates par défaut (ce mois)
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();
        
        // Location filter
        $locationId = $request->location_id;

        // Charger les locations pour le dropdown
        $locations = \App\BusinessLocation::where('business_id', $businessId)
            ->pluck('name', 'id')
            ->toArray();

        // Base query avec filtre location
        $baseQuery = MultiserviceTransaction::forBusiness($businessId)
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($locationId) {
            $baseQuery->where('location_id', $locationId);
        }

        // Statistiques générales
        $stats = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_transactions,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_transactions,
                SUM(CASE WHEN status = "canceled" THEN 1 ELSE 0 END) as canceled_transactions,
                SUM(amount) as total_amount,
                SUM(fee) as total_fees,
                SUM(profit) as total_profit
            ')
            ->first();

        // Répartition par opérateur
        $byOperator = (clone $baseQuery)
            ->select('operator')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('SUM(fee) as total_fees')
            ->selectRaw('SUM(profit) as total_profit')
            ->groupBy('operator')
            ->get();

// Répartition par type
        $byType = (clone $baseQuery)
            ->select('type_id')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('SUM(fee) as total_fees')
            ->selectRaw('SUM(profit) as total_profit')
            ->groupBy('type_id')
            ->with('transactionType')
            ->get();
        
        // Évolution journalière
        $daily = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(profit) as profit')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 10 agents
        $topAgents = (clone $baseQuery)
            ->select('user_id')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(profit) as profit')
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('profit', 'desc')
            ->limit(10)
            ->get();

        // Répartition par location (si plusieurs locations ET pas de filtre location)
        $byLocation = null;
        if (count($locations) > 1 && !$locationId) {
            $byLocation = MultiserviceTransaction::forBusiness($businessId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('location_id')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(amount) as total_amount')
                ->selectRaw('SUM(fee) as total_fees')
                ->selectRaw('SUM(profit) as total_profit')
                ->with('location')
                ->groupBy('location_id')
                ->get();
        }

        return view('multiservices::reports.index', compact(
            'stats',
            'byOperator',
            'byType',
            'daily',
            'topAgents',
            'startDate',
            'endDate',
            'locations',
            'typeNames',
            'byLocation'
        ));
    }

    public function cashReport(Request $request)
    {
        if (!auth()->user()->can('multiservices.report')) {
            abort(403, 'Accès non autorisé');
        }
    
        $businessId = auth()->user()->business_id;
        $locationId = $request->location_id ?? auth()->user()->location_id;
        
        $locations = \App\BusinessLocation::where('business_id', $businessId)->pluck('name', 'id')->toArray();
        
        if (!$locationId && count($locations) > 0) {
            $locationId = array_key_first($locations);
        }
        
        // Gestion de la période
        $period = $request->period ?? 'today';
        $today = \Carbon\Carbon::today();
        
        switch($period) {
            case 'today':
                $startDate = $today->toDateString();
                $endDate = $today->toDateString();
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay();
                $startDate = $yesterday->toDateString();
                $endDate = $yesterday->toDateString();
                break;
            case 'last_7':
                $startDate = $today->copy()->subDays(7)->toDateString();
                $endDate = $today->toDateString();
                break;
            case 'last_30':
                $startDate = $today->copy()->subDays(30)->toDateString();
                $endDate = $today->toDateString();
                break;
            case 'this_month':
                $startDate = $today->copy()->startOfMonth()->toDateString();
                $endDate = $today->toDateString();
                break;
            case 'last_month':
                $lastMonth = $today->copy()->subMonth();
                $startDate = $lastMonth->startOfMonth()->toDateString();
                $endDate = $lastMonth->endOfMonth()->toDateString();
                break;
            case 'custom':
                $startDate = $request->start_date ?? $today->toDateString();
                $endDate = $request->end_date ?? $today->toDateString();
                break;
            default:
                $startDate = $today->toDateString();
                $endDate = $today->toDateString();
        }
        
        // Chercher les caisses dans la période
        $cashRegisters = MultiservicesCashRegister::where('business_id', $businessId)
            ->where('location_id', $locationId)
            ->whereBetween('opened_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('opened_at', 'desc')
            ->get();
        
        // Si aucune caisse dans la période, prendre la dernière ouverte
        if ($cashRegisters->isEmpty()) {
            $cashRegister = MultiservicesCashRegister::where('business_id', $businessId)
                ->where('location_id', $locationId)
                ->where('status', 'open')
                ->latest('opened_at')
                ->first();
            
            if ($cashRegister) {
                $cashRegisters = collect([$cashRegister]);
            }
        }
        
        // Initialisation
        $movements = collect([]);
        $deposits = 0;
        $withdrawals = 0;
        $depositsCount = 0;
        $withdrawalsCount = 0;
        $openingAmount = 0;
        $netImpact = 0;
        $theoreticalBalance = 0;
        
        // Agréger toutes les caisses de la période
        if ($cashRegisters->isNotEmpty()) {
            foreach ($cashRegisters as $register) {
                $registerMovements = MultiservicesCashTransaction::where('cash_register_id', $register->id)
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->get();
                
                $movements = $movements->merge($registerMovements);
                $openingAmount += $register->opening_amount;
            }
            
            $movements = $movements->sortBy('created_at');

            // ✅ CALCULS AVEC ANNULATIONS
            $deposits = $movements->whereIn('type', ['deposit', 'funding'])->sum('amount');
            $withdrawals = $movements->whereIn('type', ['expense', 'withdrawal'])->sum('amount');
            
            // Soustraire les annulations de funding (remboursements)
            $fundingCancels = $movements->where('type', 'funding_cancel')->sum('amount');
            $deposits -= $fundingCancels;
            
            // Ajouter les annulations d'expense (car elles recréditent la caisse)
            $expenseCancels = $movements->where('type', 'expense_cancel')->sum('amount');
            $withdrawals -= $expenseCancels;
            
            $depositsCount = $movements->whereIn('type', ['deposit', 'funding'])->count() 
                            - $movements->where('type', 'funding_cancel')->count();
            $withdrawalsCount = $movements->whereIn('type', ['expense', 'withdrawal'])->count()
                            - $movements->where('type', 'expense_cancel')->count();
            
            $netImpact = $deposits - $withdrawals;
            $theoreticalBalance = $openingAmount + $netImpact;
        }
        
        $cashRegister = $cashRegisters->first();
        
        return view('multiservices::reports.cash', compact(
            'cashRegister', 'movements', 'deposits', 'withdrawals', 'depositsCount', 'withdrawalsCount',
            'openingAmount', 'netImpact', 'theoreticalBalance', 'locations', 'locationId', 
            'period', 'startDate', 'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->can('multiservices.report')) {
            abort(403, 'Accès non autorisé');
        }

        // TODO: Implémenter export PDF
        return response()->json(['success' => false, 'msg' => 'Export PDF à implémenter']);
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->can('multiservices.report')) {
            abort(403, 'Accès non autorisé');
        }

        // TODO: Implémenter export Excel
        return response()->json(['success' => false, 'msg' => 'Export Excel à implémenter']);
    }
}