<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Utils\Math;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class CommissionCalculator
{
    private const FREE_WITHDRAWAL_AMOUNT_CURRENCY = 'EUR';

    private TransactionRepository $transactionRepository;
    private CurrencyExchanger $currencyExchanger;

    public function __construct(TransactionRepository $transactionRepository, CurrencyExchanger $currencyExchanger)
    {
        $this->transactionRepository = $transactionRepository;
        $this->currencyExchanger = $currencyExchanger;
    }

    public function setTransactionRepository(TransactionRepository $transactionRepository): void
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function setCurrencyExchanger(CurrencyExchanger $currencyExchanger): void
    {
        $this->currencyExchanger = $currencyExchanger;
    }

    public function calculateCommission(Transaction $transaction): string
    {
        $commissionAmount = $this->getCommissionAmount($transaction);

        return $this->roundUpToDecimalPlacesOfTransactionCurrency($commissionAmount, $transaction->amount);
    }

    private function roundUpToDecimalPlacesOfTransactionCurrency(float $commissionAmount, string $transactionAmount): string
    {
        $precision = Math::decimalDigits($transactionAmount);

        return number_format(Math::roundUp($commissionAmount, $precision), $precision, '.', '');
    }

    private function getCommissionAmount(Transaction $transaction): float
    {
        if ($transaction->operationType === Transaction::DEPOSIT_OPERATION) {
            return $transaction->amount * 0.03 / 100;
        }

        if ($transaction->operationType !== Transaction::WITHDRAW_OPERATION) {
            throw new RuntimeException("Unknown operation type: $transaction->operationType");
        }

        if ($transaction->clientType === Transaction::BUSINESS_CLIENT) {
            return $transaction->amount * 0.5 / 100;
        }

        if ($transaction->clientType !== Transaction::PRIVATE_CLIENT) {
            throw new RuntimeException("Unknown user type: $transaction->clientType");
        }

        $commissionableTransactionAmount = $transaction->amount;
        $withdrawalOperationCountInWeek =
            $this->withdrawalOperationCountInWeek($transaction->clientId, $transaction->date);
        $withdrawalAmountInWeek = $this->withdrawalAmountInWeek($transaction->clientId, $transaction->date);
        if ($withdrawalOperationCountInWeek < 3 && $withdrawalAmountInWeek < 1000) {
            $transactionAmountInFreeWithdrawalAmountCurrency = $this->currencyExchanger->exchange(
                $transaction->amount,
                $transaction->currency,
                self::FREE_WITHDRAWAL_AMOUNT_CURRENCY
            );
            $freeWithdrawalAmountAvailable = 1000 - $withdrawalAmountInWeek;
            $surplusAmountAboveFreeWithdrawalAmountInFreeWithdrawalAmountCurrency = max(
                $transactionAmountInFreeWithdrawalAmountCurrency - $freeWithdrawalAmountAvailable,
                0
            );

            $commissionableTransactionAmount = $this->currencyExchanger->exchange(
                $surplusAmountAboveFreeWithdrawalAmountInFreeWithdrawalAmountCurrency,
                self::FREE_WITHDRAWAL_AMOUNT_CURRENCY,
                $transaction->currency
            );
        }

        return $commissionableTransactionAmount * 0.3 / 100;
    }

    private function withdrawalOperationCountInWeek(int $clientId, Carbon $operationDate): int
    {
        return $this->withdrawalTransactionsInWeek($clientId, $operationDate)->count();
    }

    private function withdrawalAmountInWeek(int $clientId, Carbon $operationDate): float
    {
        return (float) $this->withdrawalTransactionsInWeek($clientId, $operationDate)
                            ->map(
                                fn(Transaction $transaction) => $this->currencyExchanger->exchange(
                                    $transaction->amount,
                                    $transaction->currency,
                                    self::FREE_WITHDRAWAL_AMOUNT_CURRENCY
                                )
                            )
                            ->sum();
    }

    private function withdrawalTransactionsInWeek(int $clientId, Carbon $operationDate): Collection
    {
        return $this->transactionRepository->getAll()->filter(
            fn(Transaction $transaction) => $transaction->operationType === Transaction::WITHDRAW_OPERATION
                                            && $transaction->clientId === $clientId
                                            && $transaction->date >= $operationDate->copy()->startOfWeek()
                                            && $transaction->date <= $operationDate
        );
    }
}
