<?php

namespace Modules\Multiservices\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Multiservices\Entities\TransactionType;
use Illuminate\Support\Str;

class TransactionTypeController extends Controller
{
    /**
     * Display listing of transaction types
     */
    public function index()
    {
        if (!auth()->user()->can('multiservices.view')) {
            abort(403, 'Unauthorized action.');
        }

        // Récupérer le business_id de l'utilisateur connecté
        $businessId = auth()->user()->business_id;
        
        // DEBUG - LOGS TEMPORAIRES
        \Log::info('=== DEBUG TRANSACTION TYPES ===');
        \Log::info('User ID: ' . auth()->id());
        \Log::info('Business ID: ' . $businessId);
        
        $types = TransactionType::where('business_id', $businessId)
                                ->orderBy('name')
                                ->get();
        
        \Log::info('Types trouvés: ' . $types->count());
        \Log::info('Types: ' . json_encode($types->toArray()));
        
        return view('multiservices::transaction-types.index', compact('types'));
    }

    /**
     * Store a new transaction type
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('multiservices.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $businessId = auth()->user()->business_id;

        // Générer un code unique à partir du nom
        $code = Str::slug($request->name, '_');
        
        // Vérifier l'unicité du code
        $counter = 1;
        $originalCode = $code;
        while (TransactionType::where('code', $code)->exists()) {
            $code = $originalCode . '_' . $counter++;
        }

        TransactionType::create([
            'business_id' => $businessId,
            'name' => $request->name,
            'code' => $code,
            'icon' => $request->icon ?? 'fa-exchange',
            'color' => $request->color ?? '#3c8dbc',
            'description' => $request->description,
            'is_active' => 1,
        ]);

        return redirect()->route('multiservices.transaction-types.index')
                        ->with('status', ['success' => true, 'msg' => 'Type de transaction créé avec succès']);
    }

    /**
     * Get transaction type details (AJAX)
     */
    public function get($id)
    {
        if (!auth()->user()->can('multiservices.view')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = auth()->user()->business_id;
        
        $type = TransactionType::where('business_id', $businessId)
                              ->findOrFail($id);

        return response()->json($type);
    }

    /**
     * Update transaction type
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $businessId = auth()->user()->business_id;
        
        $type = TransactionType::where('business_id', $businessId)
                              ->findOrFail($id);

        $type->update([
            'name' => $request->name,
            'icon' => $request->icon ?? $type->icon,
            'color' => $request->color ?? $type->color,
            'description' => $request->description,
        ]);

        return redirect()->route('multiservices.transaction-types.index')
                        ->with('status', ['success' => true, 'msg' => 'Type de transaction modifié avec succès']);
    }

    /**
     * Toggle active status
     */
    public function toggle($id)
    {
        if (!auth()->user()->can('multiservices.update')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = auth()->user()->business_id;
        
        $type = TransactionType::where('business_id', $businessId)
                              ->findOrFail($id);

        $type->is_active = !$type->is_active;
        $type->save();

        $status = $type->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'success' => true,
            'message' => "Type de transaction {$status} avec succès",
            'is_active' => $type->is_active
        ]);
    }

    /**
     * Delete transaction type
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('multiservices.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $businessId = auth()->user()->business_id;
        
        $type = TransactionType::where('business_id', $businessId)
                              ->findOrFail($id);

        // Suppression directe sans vérification des transactions
        // (La vérification sera ajoutée plus tard quand les types seront liés aux transactions)
        $type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Type de transaction supprimé avec succès'
        ]);
    }
}