<?php

namespace App\Services\CommissionRules;

use App\Models\Transaction;
use RuntimeException;

class DepositRule extends CommissionRule
{
    public const DEPOSIT_CHARGE_PERCENTAGE = 0.03;

    public function calculateCommission(Transaction $transaction, ?float $commissionableTransactionAmount = null): float
    {
        if ($transaction->operationType === Transaction::DEPOSIT_OPERATION) {
            return $transaction->amount * self::DEPOSIT_CHARGE_PERCENTAGE / 100;
        }

        if ($this->nextRuleIfFailed === null) {
            throw new RuntimeException('Next Rule if Failed is not set.');
        }

        return $this->nextRuleIfFailed->calculateCommission($transaction, $commissionableTransactionAmount);
    }
}
