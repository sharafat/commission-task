<?php

namespace App\Services\CommissionRules;

use App\Models\Transaction;
use App\Services\CurrencyExchanger;
use Carbon\Carbon;
use RuntimeException;

class PrivateClientFreeWithdrawalAmountPerWeekRule extends CommissionRule
{
    use WithdrawalTransactionsInWeekTrait;

    public const MAX_FREE_WITHDRAWAL_AMOUNT_PER_WEEK = 1000;
    public const MAX_FREE_WITHDRAWAL_AMOUNT_CURRENCY = 'EUR';

    private CurrencyExchanger $currencyExchanger;

    public function setCurrencyExchanger(CurrencyExchanger $currencyExchanger): void
    {
        $this->currencyExchanger = $currencyExchanger;
    }

    public function calculateCommission(Transaction $transaction, ?float $commissionableTransactionAmount = null): float
    {
        if ($transaction->operationType === Transaction::WITHDRAW_OPERATION
            && $transaction->clientType === Transaction::PRIVATE_CLIENT
            && $this->withdrawalAmountInWeek($transaction->clientId, $transaction->date)
               < self::MAX_FREE_WITHDRAWAL_AMOUNT_PER_WEEK) {

            $transactionAmountInFreeWithdrawalAmountCurrency = $this->currencyExchanger->exchange(
                $transaction->amount,
                $transaction->currency,
                self::MAX_FREE_WITHDRAWAL_AMOUNT_CURRENCY
            );
            $freeWithdrawalAmountAvailable = self::MAX_FREE_WITHDRAWAL_AMOUNT_PER_WEEK
                                             - $this->withdrawalAmountInWeek($transaction->clientId, $transaction->date);
            $surplusAmountAboveFreeWithdrawalAmountInFreeWithdrawalAmountCurrency = max(
                $transactionAmountInFreeWithdrawalAmountCurrency - $freeWithdrawalAmountAvailable,
                0
            );

            $commissionableTransactionAmount = $this->currencyExchanger->exchange(
                $surplusAmountAboveFreeWithdrawalAmountInFreeWithdrawalAmountCurrency,
                self::MAX_FREE_WITHDRAWAL_AMOUNT_CURRENCY,
                $transaction->currency
            );

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

    private function withdrawalAmountInWeek(int $clientId, Carbon $operationDate): float
    {
        return (float) $this->withdrawalTransactionsInWeek($clientId, $operationDate, $this->transactionRepository)
                            ->map(
                                fn(Transaction $transaction) => $this->currencyExchanger->exchange(
                                    $transaction->amount,
                                    $transaction->currency,
                                    self::MAX_FREE_WITHDRAWAL_AMOUNT_CURRENCY
                                )
                            )
                            ->sum();
    }
}
