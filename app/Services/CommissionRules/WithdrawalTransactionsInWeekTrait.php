<?php

namespace App\Services\CommissionRules;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait WithdrawalTransactionsInWeekTrait
{
    private function withdrawalTransactionsInWeek(
        int $clientId,
        Carbon $operationDate,
        TransactionRepository $transactionRepository
    ): Collection {
        return $transactionRepository->getAll()->filter(
            fn(Transaction $transaction) => $transaction->operationType === Transaction::WITHDRAW_OPERATION
                                            && $transaction->clientId === $clientId
                                            && $transaction->date >= $operationDate->copy()->startOfWeek()
                                            && $transaction->date <= $operationDate
        );
    }
}
