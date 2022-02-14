<?php

namespace App\Services\CommissionRules;

use App\Models\Transaction;
use Carbon\Carbon;
use RuntimeException;

class PrivateClientFreeWithdrawalCountPerWeekRule extends CommissionRule
{
    use WithdrawalTransactionsInWeekTrait;

    public const MAX_FREE_WITHDRAWALS_PER_WEEK = 3;

    public function calculateCommission(Transaction $transaction, ?float $commissionableTransactionAmount = null): float
    {
        if ($transaction->operationType === Transaction::WITHDRAW_OPERATION
            && $transaction->clientType === Transaction::PRIVATE_CLIENT
            && $this->withdrawalOperationCountInWeek($transaction->clientId, $transaction->date)
               < self::MAX_FREE_WITHDRAWALS_PER_WEEK) {

            if ($this->nextRuleIfPassed === null) {
                throw new RuntimeException('Next Rule if Passed is not set.');
            }

            return $this->nextRuleIfPassed->calculateCommission($transaction, $commissionableTransactionAmount);
        }

        if ($this->nextRuleIfFailed === null) {
            throw new RuntimeException('Next Rule if Failed is not set.');
        }

        return $this->nextRuleIfFailed->calculateCommission($transaction, $commissionableTransactionAmount);
    }

    private function withdrawalOperationCountInWeek(int $clientId, Carbon $operationDate): int
    {
        return $this->withdrawalTransactionsInWeek($clientId, $operationDate, $this->transactionRepository)->count();
    }
}
