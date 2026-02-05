<?php

namespace Modules\Multiservices\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Modules\Multiservices\Entities\OperatorAccount;
use Modules\Multiservices\Entities\OperatorAccountTransaction;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Carbon\Carbon;

class OperatorAccountController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('multiservices.view')) {
            abort(403, 'Accès non autorisé');
        }

        $businessId = auth()->user()->business_id;

        // Si requête AJAX pour DataTables
        if ($request->ajax() && $request->table == 'accounts') {
            $accounts = OperatorAccount::forBusiness($businessId)
                ->select('operator_accounts.*');

            return DataTables::of($accounts)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    $html .= '<button class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>';
                    $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                    $html .= '<li><a href="#" class="fund-account" data-id="' . $row->id . '"><i class="fa fa-money"></i> Alimenter</a></li>';
                    $html .= '<li><a href="' . route('multiservices.accounts.history', $row->id) . '"><i class="fa fa-history"></i> Historique</a></li>';
                    $html .= '<li><a href="' . route('operator-accounts.adjust', $row->id) . '">';
                    $html .= '<i class="fa fa-balance-scale"></i> Ajuster Solde</a></li>';
                    if (auth()->user()->can('multiservices.update')) {
                        $html .= '<li><a href="' . route('multiservices.accounts.edit', $row->id) . '"><i class="fa fa-edit"></i> Modifier</a></li>';
                    }
                    if (auth()->user()->can('multiservices.delete')) {
                        $html .= '<li><a href="#" class="delete-account" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Supprimer</a></li>';
                    }
                    $html .= '</ul></div>';
                    return $html;
                })
                ->addColumn('location', function ($row) {
                    return $row->location ? $row->location->name : '-';
                })
                ->editColumn('balance', function ($row) {
                    $color = $row->balance > 0 ? 'text-green' : 'text-red';
                    return '<strong class="' . $color . '">' . number_format($row->balance, 0) . ' FCFA</strong>';
                })
                ->editColumn('operator', function ($row) {
                    return $row->operator_name;
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active ? '<span class="label label-success">Actif</span>' : '<span class="label label-default">Inactif</span>';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'balance', 'is_active'])
                ->make(true);
        }

        // Pour les rapports
        if ($request->tab == 'reports') {
            $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
            $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();
            $locationId = $request->location_id;

            // Query de base pour les comptes
            $accountsQuery = OperatorAccount::forBusiness($businessId);
            
            if ($locationId) {
                $accountsQuery->where('location_id', $locationId);
            }

            // Stats globales
            $stats = [
                'total_balance' => (clone $accountsQuery)->sum('balance'),
                'total_accounts' => (clone $accountsQuery)->where('is_active', true)->count(),
                'total_deposits' => OperatorAccountTransaction::whereHas('account', function($q) use ($businessId, $locationId) {
                    $q->where('business_id', $businessId);
                    if ($locationId) {
                        $q->where('location_id', $locationId);
                    }
                })->where('type', 'deposit')->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
                'total_withdrawals' => OperatorAccountTransaction::whereHas('account', function($q) use ($businessId, $locationId) {
                    $q->where('business_id', $businessId);
                    if ($locationId) {
                        $q->where('location_id', $locationId);
                    }
                })->where('type', 'withdrawal')->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
            ];

            // Par opérateur
            $byOperator = (clone $accountsQuery)
                ->select('operator', DB::raw('SUM(balance) as total_balance'), DB::raw('COUNT(*) as count'))
                ->groupBy('operator')
                ->get();

            // Par location (si pas de filtre location et plusieurs locations)
            $byLocation = null;
            $locationsCount = \App\BusinessLocation::where('business_id', $businessId)->count();
            
            if (!$locationId && $locationsCount > 1) {
                $byLocation = OperatorAccount::forBusiness($businessId)
                    ->join('business_locations', 'operator_accounts.location_id', '=', 'business_locations.id')
                    ->select(
                        'operator_accounts.location_id',
                        'business_locations.name as location_name',
                        DB::raw('SUM(operator_accounts.balance) as total_balance'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->groupBy('operator_accounts.location_id', 'business_locations.name')
                    ->get();
            }

            return view('multiservices::accounts.reports', compact('stats', 'byOperator', 'byLocation', 'startDate', 'endDate'));
        }

        $operators = \Modules\Multiservices\Entities\Operator::getOperatorsForBusiness($businessId)
            ->mapWithKeys(function($op) {
                return [$op->code => ['name' => $op->name]];
            })
            ->toArray();

        // Charger les locations
        $locations = \App\BusinessLocation::forDropdown($businessId, false);

        return view('multiservices::accounts.index', compact('operators', 'locations'));
    }

    public function create()
    {
        if (!auth()->user()->can('multiservices.create')) {
            abort(403, 'Accès non autorisé');
        }

        $businessId = auth()->user()->business_id;

        $operators = \Modules\Multiservices\Entities\Operator::getOperatorsForBusiness($businessId)
            ->mapWithKeys(function($op) {
                return [$op->code => ['name' => $op->name]];
            })
            ->toArray();

        return view('multiservices::accounts.create', compact('operators'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('multiservices.create')) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'operator' => 'required|string|max:50',
            'location_id' => 'required|exists:business_locations,id',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        $account = OperatorAccount::create([
            'business_id' => auth()->user()->business_id,
            'location_id' => $request->location_id,
            'operator' => $request->operator,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'balance' => $request->initial_balance,
            'initial_balance' => $request->initial_balance,
            'notes' => $request->notes,
        ]);

        // Enregistrer le mouvement initial
        if ($request->initial_balance > 0) {
            OperatorAccountTransaction::create([
                'operator_account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $request->initial_balance,
                'balance_before' => 0,
                'balance_after' => $request->initial_balance,
                'notes' => 'Solde initial',
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Compte créé avec succès'
        ]);
    }

    public function fund(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0',
        ]);

        $account = OperatorAccount::forBusiness(auth()->user()->business_id)->findOrFail($id);

        if ($request->type == 'deposit') {
            $account->deposit($request->amount, $request->notes, auth()->id());
        } else {
            $account->withdraw($request->amount, $request->notes, auth()->id());
        }

        return response()->json([
            'success' => true,
            'msg' => 'Opération effectuée avec succès',
            'new_balance' => $account->balance
        ]);
    }

    public function history(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.view')) {
            abort(403, 'Accès non autorisé');
        }

        $account = OperatorAccount::forBusiness(auth()->user()->business_id)->findOrFail($id);

        // Query de base
        $query = OperatorAccountTransaction::where('operator_account_id', $id);

        // Filtres
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('multiservices::accounts.history', compact('account', 'transactions'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Accès non autorisé');
        }

        $account = OperatorAccount::forBusiness(auth()->user()->business_id)->findOrFail($id);
        
        $businessId = auth()->user()->business_id;
        
        $operators = \Modules\Multiservices\Entities\Operator::getOperatorsForBusiness($businessId)
            ->mapWithKeys(function($op) {
                return [$op->code => ['name' => $op->name]];
            })
            ->toArray();

        return view('multiservices::accounts.edit', compact('account', 'operators'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
        ]);

        $account = OperatorAccount::forBusiness(auth()->user()->business_id)->findOrFail($id);
        
        $account->update([
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'notes' => $request->notes,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('multiservices.accounts.index')->with('success', 'Compte modifié avec succès');
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('multiservices.delete')) {
            abort(403, 'Accès non autorisé');
        }

        $account = OperatorAccount::forBusiness(auth()->user()->business_id)->findOrFail($id);
        $account->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Compte supprimé avec succès'
        ]);
    }
    /**
     * Afficher formulaire d'ajustement
     */
    public function showAdjustForm($id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Accès non autorisé');
        }
        
        $account = OperatorAccount::where('business_id', auth()->user()->business_id)
            ->findOrFail($id);
        
        return view('multiservices::accounts.adjust', compact('account'));
    }
    
    /**
     * Traiter l'ajustement
     */
    public function processAdjustment(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Accès non autorisé');
        }
        
        $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|min:5|max:500',
        ], [
            'reason.min' => 'Le motif doit contenir au moins 5 caractères',
            'reason.required' => 'Le motif est obligatoire',
            'amount.min' => 'Le montant doit être supérieur à 0',
        ]);
        
        try {
            $account = OperatorAccount::where('business_id', auth()->user()->business_id)
                ->findOrFail($id);
            
            $account->adjustBalance(
                $request->amount,
                $request->type,
                $request->reason
            );
            
            $action = $request->type === 'credit' ? 'augmenté' : 'diminué';
            $output = [
                'success' => true,
                'msg' => "Solde {$action} de " . number_format($request->amount, 0, ',', ' ') . " FCFA avec succès"
            ];
            
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        
        return redirect()
            ->route('multiservices.accounts.index')
            ->with('status', $output);
    }
}