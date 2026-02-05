<?php

namespace Modules\Multiservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MultiservicesCashRegister extends Model
{
    use SoftDeletes;
    
    protected $table = 'multiservices_cash_registers';

    protected $fillable = [
        'business_id', 'location_id', 'user_id', 'status',
        'opening_amount', 'expected_amount', 'closing_amount',
        'shortage', 'excess', 'opened_at', 'closed_at', 'closed_by',
        'opening_notes', 'closing_notes'
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'shortage' => 'decimal:2',
        'excess' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relations
    public function business()
    {
        return $this->belongsTo('App\Business');
    }

    public function location()
    {
        return $this->belongsTo('App\BusinessLocation');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function transactions()
    {
        return $this->hasMany(MultiservicesCashTransaction::class, 'cash_register_id');
    }

    // Scopes
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Helpers
    public static function getOrCreateOpen($businessId, $locationId)
    {
        $register = self::where('business_id', $businessId)
            ->where('location_id', $locationId)
            ->where('status', 'open')
            ->whereDate('opened_at', today())
            ->first();

        if (!$register) {
            $register = self::create([
                'business_id' => $businessId,
                'location_id' => $locationId,
                'user_id' => auth()->id(),
                'status' => 'open',
                'opening_amount' => 0,
                'expected_amount' => 0,
                'opened_at' => now(),
            ]);
        }

        return $register;
    }
    /**
     * Prélèvement dans la caisse (sortie d'argent)
     * 
     * @param float $amount Montant à prélever
     * @param string $motif Raison du prélèvement (obligatoire)
     * @param string|null $beneficiary Bénéficiaire (optionnel)
     * @return $this
     * @throws \Exception Si solde insuffisant
     */
    public function addExpense($amount, $motif, $beneficiary = null)
    {
        // Validation solde
        if ($this->expected_amount < $amount) {
            throw new \Exception(
                "Solde insuffisant. Solde actuel : " . 
                number_format($this->expected_amount, 0, ',', ' ') . " FCFA"
            );
        }
        
        // Validation motif
        if (empty($motif) || strlen($motif) < 5) {
            throw new \Exception("Le motif doit contenir au moins 5 caractères");
        }
        
        // Construction notes complètes
        $notes = "Motif : {$motif}";
        if (!empty($beneficiary)) {
            $notes .= " | Bénéficiaire : {$beneficiary}";
        }
        
        // Sauvegarde solde avant
        $balanceBefore = $this->expected_amount;
        
        // Mise à jour solde (débit)
        $this->expected_amount -= $amount;
        $this->save();
        
        // Enregistrement transaction
        MultiservicesCashTransaction::create([
            'cash_register_id' => $this->id,
            'type' => 'expense',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->expected_amount,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
        
        // Log
        \Log::info(
            "💸 PRÉLÈVEMENT - Caisse #{$this->id} : " . 
            number_format($amount, 0) . " FCFA - {$motif} par " . 
            auth()->user()->name
        );
        
        return $this;
    }
    public function addDeposit($amount, $transactionId = null, $notes = null)
    {
        $balanceBefore = $this->expected_amount;
        $this->expected_amount += $amount;
        $this->save();

        return MultiservicesCashTransaction::create([
            'cash_register_id' => $this->id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->expected_amount,
            'multiservice_transaction_id' => $transactionId,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    public function addWithdrawal($amount, $transactionId = null, $notes = null)
    {
        $balanceBefore = $this->expected_amount;
        $this->expected_amount -= $amount;
        $this->save();

        return MultiservicesCashTransaction::create([
            'cash_register_id' => $this->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->expected_amount,
            'multiservice_transaction_id' => $transactionId,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    public function addFunding($amount, $notes = null)
    {
        $balanceBefore = $this->expected_amount;
        $this->expected_amount += $amount;
        $this->save();

        return MultiservicesCashTransaction::create([
            'cash_register_id' => $this->id,
            'type' => 'funding',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->expected_amount,
            'notes' => $notes ?? 'Alimentation caisse',
            'created_by' => auth()->id(),
        ]);
    }
    /**
     * Annuler une alimentation (inverse le mouvement)
     */
    public function cancelFunding($transactionId, $reason)
    {
        $transaction = MultiservicesCashTransaction::where('id', $transactionId)
            ->where('cash_register_id', $this->id)
            ->where('type', 'funding')
            ->firstOrFail();
        
        if ($this->expected_amount < $transaction->amount) {
            throw new \Exception("Solde insuffisant pour annuler cette alimentation");
        }
        
        $balanceBefore = $this->expected_amount;
        $this->expected_amount -= $transaction->amount;
        $this->save();
        
        // Créer mouvement inverse
        MultiservicesCashTransaction::create([
            'cash_register_id' => $this->id,
            'type' => 'funding_cancel',
            'amount' => $transaction->amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->expected_amount,
            'notes' => "Annulation alimentation - " . $reason,
            'reference' => $transaction->id,
            'created_by' => auth()->id(),
        ]);
        
        // Marquer transaction originale comme annulée
        $transaction->update(['notes' => $transaction->notes . ' [ANNULÉ]']);
        
        \Log::info("🔄 ANNULATION ALIMENTATION - Caisse #{$this->id} : " . number_format($transaction->amount, 0) . " FCFA");
        
        return $this;
    }
    
    /**
     * Annuler un prélèvement (inverse le mouvement)
     */
    public function cancelExpense($transactionId, $reason)
    {
        $transaction = MultiservicesCashTransaction::where('id', $transactionId)
            ->where('cash_register_id', $this->id)
            ->where('type', 'expense')
            ->firstOrFail();
        
        $balanceBefore = $this->expected_amount;
        $this->expected_amount += $transaction->amount;
        $this->save();
        
        // Créer mouvement inverse
        MultiservicesCashTransaction::create([
            'cash_register_id' => $this->id,
            'type' => 'expense_cancel',
            'amount' => $transaction->amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->expected_amount,
            'notes' => "Annulation prélèvement - " . $reason,
            'reference' => $transaction->id,
            'created_by' => auth()->id(),
        ]);
        
        // Marquer transaction originale comme annulée
        $transaction->update(['notes' => $transaction->notes . ' [ANNULÉ]']);
        
        \Log::info("🔄 ANNULATION PRÉLÈVEMENT - Caisse #{$this->id} : " . number_format($transaction->amount, 0) . " FCFA");
        
        return $this;
    }
    
    /**
     * Supprimer un prélèvement (suppression physique)
     */
    public function deleteExpense($transactionId)
    {
        $transaction = MultiservicesCashTransaction::where('id', $transactionId)
            ->where('cash_register_id', $this->id)
            ->where('type', 'expense')
            ->firstOrFail();
        
        // Rembourser le montant
        $this->expected_amount += $transaction->amount;
        $this->save();
        
        \Log::info("🗑️ SUPPRESSION PRÉLÈVEMENT - Caisse #{$this->id} : " . number_format($transaction->amount, 0) . " FCFA");
        
        // Supprimer la transaction
        $transaction->delete();
        
        return $this;
    }
}
