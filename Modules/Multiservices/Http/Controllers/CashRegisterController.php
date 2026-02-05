<?php

namespace Modules\Multiservices\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Multiservices\Entities\MultiservicesCashRegister;
use Modules\Multiservices\Entities\MultiservicesCashTransaction;
use DB;

class CashRegisterController extends Controller
{
    /**
     * Liste des caisses
     */
    public function index(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $locationId = $request->location_id ?? auth()->user()->location_id;
        
        $registers = MultiservicesCashRegister::where('business_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $locations = \App\BusinessLocation::where('business_id', $businessId)
            ->pluck('name', 'id');
        
        return view('multiservices::cash-register.index', compact('registers', 'locations', 'locationId'));
    }
    /**
     * Afficher formulaire de prélèvement
     */
    public function showExpenseForm($id)
    {
        $register = MultiservicesCashRegister::with('business')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);
        
        // Vérifier que la caisse est ouverte
        if ($register->status !== 'open') {
            return redirect()->back()->with('status', [
                'success' => false,
                'msg' => 'Cette caisse est fermée. Impossible de faire un prélèvement.'
            ]);
        }
        
        return view('multiservices::cash-register.expense', compact('register'));
    }
    
    /**
     * Traiter le prélèvement
     */
    public function processExpense(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'motif' => 'required|string|min:5|max:500',
            'beneficiary' => 'nullable|string|max:200',
        ], [
            'amount.required' => 'Le montant est obligatoire',
            'amount.min' => 'Le montant doit être supérieur à 0',
            'motif.required' => 'Le motif est obligatoire',
            'motif.min' => 'Le motif doit contenir au moins 5 caractères',
        ]);
        
        try {
            $register = MultiservicesCashRegister::where('business_id', auth()->user()->business_id)
                ->findOrFail($id);
            
            // Vérifier statut
            if ($register->status !== 'open') {
                throw new \Exception('Cette caisse est fermée');
            }
            
            // Prélèvement
            $register->addExpense(
                $request->amount,
                $request->motif,
                $request->beneficiary
            );
            
            $output = [
                'success' => true,
                'msg' => 'Prélèvement de ' . number_format($request->amount, 0) . ' FCFA effectué avec succès'
            ];
            
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        
        // ⭐ LIGNE CORRIGÉE
        return redirect()->route('cash-register.show', $id)->with('status', $output);
    }

    /**
     * Ouvrir une caisse
     */
    public function create()
    {
        $businessId = auth()->user()->business_id;
        
        // Vérifier si une caisse est déjà ouverte aujourd'hui
        $openRegister = MultiservicesCashRegister::where('business_id', $businessId)
            ->where('location_id', auth()->user()->location_id)
            ->where('status', 'open')
            ->whereDate('opened_at', today())
            ->first();
        
        if ($openRegister) {
            return redirect()->route('cash-register.show', $openRegister->id)
                ->with('warning', 'Une caisse est déjà ouverte pour aujourd\'hui');
        }
        
        $locations = \App\BusinessLocation::where('business_id', $businessId)
            ->pluck('name', 'id');
        
        return view('multiservices::cash-register.create', compact('locations'));
    }

    /**
     * Enregistrer l'ouverture
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:business_locations,id',
            'opening_amount' => 'required|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            $businessId = auth()->user()->business_id;
            
            $register = MultiservicesCashRegister::create([
                'business_id' => $businessId,
                'location_id' => $request->location_id,
                'user_id' => auth()->id(),
                'status' => 'open',
                'opening_amount' => $request->opening_amount,
                'expected_amount' => $request->opening_amount,
                'opened_at' => now(),
                'opening_notes' => $request->opening_notes,
            ]);
            
            // Enregistrer le mouvement d'ouverture
            MultiservicesCashTransaction::create([
                'cash_register_id' => $register->id,
                'type' => 'opening',
                'amount' => $request->opening_amount,
                'balance_before' => 0,
                'balance_after' => $request->opening_amount,
                'notes' => 'Ouverture caisse - ' . ($request->opening_notes ?? ''),
                'created_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            return redirect()->route('cash-register.show', $register->id)
                ->with('success', 'Caisse ouverte avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Voir une caisse
     */
    public function show($id)
    {
        $register = MultiservicesCashRegister::with(['transactions' => function($q) {
            $q->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        return view('multiservices::cash-register.show', compact('register'));
    }

    /**
     * Alimenter la caisse
     */
    public function fund(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        
        try {
            $register = MultiservicesCashRegister::where('business_id', auth()->user()->business_id)
                ->findOrFail($id);
            
            if ($register->status !== 'open') {
                throw new \Exception('Cette caisse est fermée');
            }
            
            $register->addFunding($request->amount, $request->notes);
            
            $output = [
                'success' => true,
                'msg' => 'Caisse alimentée avec succès'
            ];
            
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        
        // ⭐ REDIRECTION AU LIEU DE JSON
        return redirect()
            ->route('cash-register.show', $id)
            ->with('status', $output);
    }

    /**
     * Fermer la caisse
     */
    public function close(Request $request, $id)
    {
        $request->validate([
            'closing_amount' => 'required|numeric|min:0',
        ]);
        
        try {
            DB::beginTransaction();
            
            $register = MultiservicesCashRegister::findOrFail($id);
            
            if ($register->status !== 'open') {
                return redirect()->back()->with('error', 'Cette caisse est déjà fermée');
            }
            
            $closingAmount = $request->closing_amount;
            $expectedAmount = $register->expected_amount;
            $difference = $closingAmount - $expectedAmount;
            
            $register->update([
                'status' => 'closed',
                'closing_amount' => $closingAmount,
                'shortage' => $difference < 0 ? abs($difference) : 0,
                'excess' => $difference > 0 ? $difference : 0,
                'closed_at' => now(),
                'closed_by' => auth()->id(),
                'closing_notes' => $request->closing_notes,
            ]);
            
            // Enregistrer le mouvement de fermeture
            MultiservicesCashTransaction::create([
                'cash_register_id' => $register->id,
                'type' => 'closing',
                'amount' => $closingAmount,
                'balance_before' => $expectedAmount,
                'balance_after' => $closingAmount,
                'notes' => 'Fermeture caisse - ' . ($request->closing_notes ?? ''),
                'created_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            return redirect()->route('cash-register.show', $register->id)
                ->with('success', 'Caisse fermée avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
    /**
     * Annuler une alimentation
     */
    public function cancelFunding(Request $request, $registerId, $transactionId)
    {
        if (!auth()->user()->can('multiservices.update')) {
            return response()->json(['success' => false, 'msg' => 'Accès non autorisé']);
        }
        
        try {
            $register = MultiservicesCashRegister::where('business_id', auth()->user()->business_id)
                ->findOrFail($registerId);
            
            if ($register->status !== 'open') {
                throw new \Exception('Cette caisse est fermée');
            }
            
            $register->cancelFunding($transactionId, $request->reason ?? 'Annulation manuelle');
            
            return response()->json([
                'success' => true,
                'msg' => 'Alimentation annulée avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Annuler un prélèvement
     */
    public function cancelExpense(Request $request, $registerId, $transactionId)
    {
        if (!auth()->user()->can('multiservices.update')) {
            return response()->json(['success' => false, 'msg' => 'Accès non autorisé']);
        }
        
        try {
            $register = MultiservicesCashRegister::where('business_id', auth()->user()->business_id)
                ->findOrFail($registerId);
            
            if ($register->status !== 'open') {
                throw new \Exception('Cette caisse est fermée');
            }
            
            $register->cancelExpense($transactionId, $request->reason ?? 'Annulation manuelle');
            
            return response()->json([
                'success' => true,
                'msg' => 'Prélèvement annulé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Supprimer un prélèvement
     */
    public function deleteExpense($registerId, $transactionId)
    {
        if (!auth()->user()->can('multiservices.delete')) {
            return response()->json(['success' => false, 'msg' => 'Accès non autorisé']);
        }
        
        try {
            $register = MultiservicesCashRegister::where('business_id', auth()->user()->business_id)
                ->findOrFail($registerId);
            
            if ($register->status !== 'open') {
                throw new \Exception('Cette caisse est fermée');
            }
            
            $register->deleteExpense($transactionId);
            
            return response()->json([
                'success' => true,
                'msg' => 'Prélèvement supprimé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
        }
    }
}
