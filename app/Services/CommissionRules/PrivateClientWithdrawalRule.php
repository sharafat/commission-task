<?php

namespace App\Services\CommissionRules;

use App\Models\Transaction;
use RuntimeException;

class PrivateClientWithdrawalRule extends CommissionRule
{
    public const WITHDRAWAL_CHARGE_PERCENTAGE = 0.3;

    public function calculateCommission(Transaction $transaction, ?float $commissionableTransactionAmount = null): float
    {
        if ($transaction->operationType === Transaction::WITHDRAW_OPERATION
            && $transaction->clientType === Transaction::PRIVATE_CLIENT) {
            return ($commissionableTransactionAmount ?? (float) $transaction->amount) * self::WITHDRAWAL_CHARGE_PERCENTAGE / 100;
        }

        if ($this->nextRuleIfFailed === null) {
            throw new RuntimeException('Next Rule if Failed is not set.');
        }

        return $this->nextRuleIfFailed->calculateCommission($transaction, $commissionableTransactionAmount);
    }
}
