<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatorAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'operator',
        'location_id',
        'account_name',
        'account_number',
        'balance',
        'initial_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo('App\Business');
    }
    
    public function location()
    {
        return $this->belongsTo('App\BusinessLocation', 'location_id');
    }
    
    public function transactions()
    {
        return $this->hasMany(OperatorAccountTransaction::class);
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
    
    public function scopeForLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }
    /**
     * Ajuster le solde du compte (correction comptable)
     * 
     * @param float $amount Montant de l'ajustement
     * @param string $type 'credit' ou 'debit'
     * @param string $reason Motif de l'ajustement (obligatoire)
     * @return $this
     * @throws \Exception
     */
    public function adjustBalance($amount, $type, $reason)
    {
        $balanceBefore = $this->balance;
        
        // Validation
        if ($type === 'debit' && $this->balance < $amount) {
            throw new \Exception("Solde insuffisant. Solde actuel: " . number_format($this->balance, 0) . " FCFA");
        }
        
        if (empty($reason) || strlen($reason) < 5) {
            throw new \Exception("Le motif doit contenir au moins 5 caractères");
        }
        
        // Mise à jour solde
        if ($type === 'credit') {
            $this->balance += $amount;
        } else {
            $this->balance -= $amount;
        }
        
        $this->save();
        
        // Enregistrement historique avec tracking
        OperatorAccountTransaction::create([
            'operator_account_id' => $this->id,
            'type' => 'adjustment_' . $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'notes' => $reason,
            'created_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        \Log::info("✏️ AJUSTEMENT MANUEL - Compte #{$this->id} : {$type} de " . number_format($amount, 0) . " FCFA par " . auth()->user()->name);
        
        return $this;
    }
    /**
     * Afficher formulaire d'ajustement
     */
    public function showAdjustForm($id)
    {
        $account = OperatorAccount::with('operator')
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($id);
        
        return view('multiservices::operator-accounts.adjust', compact('account'));
    }
    
    /**
     * Traiter l'ajustement
     */
    public function processAdjustment(Request $request, $id)
    {
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
                'msg' => "Solde {$action} de " . number_format($request->amount, 0) . " FCFA avec succès"
            ];
            
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        
        return redirect()
            ->route('operator-accounts.show', $id)
            ->with('status', $output);
    }
    /**
     * Débiter le compte (diminuer le solde)
     */
    public function debit($amount, $notes = null)
    {
        $balanceBefore = $this->balance;
        
        if ($this->balance < $amount) {
            throw new \Exception("Solde insuffisant. Solde actuel : " . number_format($this->balance, 0) . " FCFA");
        }
        
        $this->balance -= $amount;
        $this->save();
        
        OperatorAccountTransaction::create([
            'operator_account_id' => $this->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }
    
    /**
     * Créditer le compte (augmenter le solde)
     */
    public function credit($amount, $notes = null)
    {
        $balanceBefore = $this->balance;
        
        $this->balance += $amount;
        $this->save();
        
        OperatorAccountTransaction::create([
            'operator_account_id' => $this->id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }
}