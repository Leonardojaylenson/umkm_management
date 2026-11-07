<?php

namespace App\Models\Concerns;

use Illuminate\Validation\ValidationException;

trait HasInstallments
{
    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday();
    }

    public function addPayment(float $amount, string $date, ?string $note = null): void
    {
        if ($amount <= 0 || $amount > $this->remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Jumlah bayar harus lebih dari 0 dan tidak melebihi sisa (' . rupiah($this->remaining) . ').',
            ]);
        }

        $this->payments()->create([
            'id_user'      => auth()->id(),
            'amount'       => $amount,
            'payment_date' => $date,
            'note'         => $note,
        ]);

        $this->refreshStatus();
        $this->afterPayment($amount);
    }

    public function refreshStatus(): void
    {
        $paid = (float) $this->payments()->sum('amount');

        $this->paid_amount = $paid;
        $this->status = $paid >= (float) $this->amount ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');
        $this->save();
    }

    protected function afterPayment(float $amount): void
    {
    }
}
