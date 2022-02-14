<?php

namespace App\Services\CommissionRules;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;

abstract class CommissionRule
{
    protected ?CommissionRule $nextRuleIfPassed;
    protected ?CommissionRule $nextRuleIfFailed;
    protected TransactionRepository $transactionRepository;

    public function __construct(
        ?CommissionRule $nextRuleIfPassed = null,
        ?CommissionRule $nextRuleIfFailed = null,
        ?TransactionRepository $transactionRepository = null,
    ) {
        $this->nextRuleIfPassed = $nextRuleIfPassed;
        $this->nextRuleIfFailed = $nextRuleIfFailed;
        $this->transactionRepository = $transactionRepository;
    }

    abstract public function calculateCommission(
        Transaction $transaction,
        ?float $commissionableTransactionAmount = null
    ): float;
}
