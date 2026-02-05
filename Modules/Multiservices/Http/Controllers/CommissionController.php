<?php

namespace Modules\Multiservices\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Multiservices\Entities\MultiserviceCommission;

class CommissionController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('multiservices.settings')) {
            abort(403, 'Accès non autorisé');
        }

        $businessId = auth()->user()->business_id;
        $commissions = MultiserviceCommission::forBusiness($businessId)
            ->orderBy('operator')
            ->orderBy('priority', 'desc')
            ->get();

        $operators = \Modules\Multiservices\Entities\Operator::getOperatorsForBusiness($businessId)
            ->mapWithKeys(function($op) {
                return [$op->code => ['name' => $op->name, 'color' => $op->color]];
            })
            ->toArray();
        $transactionTypes = config('multiservices.transaction_types');

        return view('multiservices::commissions.index', compact('commissions', 'operators', 'transactionTypes'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'operator' => 'required|in:wave,orange_money,ria,moneygram,western_union,autres',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer,all',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $commission = MultiserviceCommission::create([
            'business_id' => auth()->user()->business_id,
            'operator' => $request->operator,
            'transaction_type' => $request->transaction_type,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'min_commission' => $request->min_commission,
            'max_commission' => $request->max_commission,
            'priority' => $request->priority ?? 0,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'msg' => 'Commission créée avec succès']);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            abort(403, 'Accès non autorisé');
        }

        $commission = MultiserviceCommission::forBusiness(auth()->user()->business_id)->findOrFail($id);

        $request->validate([
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $commission->update([
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'min_commission' => $request->min_commission,
            'max_commission' => $request->max_commission,
            'priority' => $request->priority ?? 0,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'msg' => 'Commission modifiée avec succès']);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            abort(403, 'Accès non autorisé');
        }

        $commission = MultiserviceCommission::forBusiness(auth()->user()->business_id)->findOrFail($id);
        $commission->delete();

        return response()->json(['success' => true, 'msg' => 'Commission supprimée avec succès']);
    }

    public function toggleActive($id)
    {
        if (!auth()->user()->can('multiservices.settings')) {
            abort(403, 'Accès non autorisé');
        }

        $commission = MultiserviceCommission::forBusiness(auth()->user()->business_id)->findOrFail($id);
        $commission->is_active = !$commission->is_active;
        $commission->save();

        return response()->json([
            'success' => true,
            'msg' => $commission->is_active ? 'Commission activée' : 'Commission désactivée',
            'is_active' => $commission->is_active
        ]);
    }
}