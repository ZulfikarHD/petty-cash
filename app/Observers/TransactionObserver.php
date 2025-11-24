<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "creating" event.
     */
    public function creating(Transaction $transaction): void
    {
        if (empty($transaction->transaction_number)) {
            $transaction->transaction_number = $this->generateTransactionNumber();
        }
    }

    /**
     * Generate a unique transaction number.
     */
    private function generateTransactionNumber(): string
    {
        $year = date('Y');
        $prefix = "TXN-{$year}-";

        // Get the last transaction number for the current year
        $lastTransaction = Transaction::query()
            ->where('transaction_number', 'like', "{$prefix}%")
            ->orderByDesc('transaction_number')
            ->first();

        if ($lastTransaction) {
            // Extract the numeric part and increment
            $lastNumber = (int) substr($lastTransaction->transaction_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
