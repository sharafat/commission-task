<?php

namespace App;

use App\Repositories\TransactionRepository;
use App\Services\CommissionCalculator;
use App\Utils\TransactionRecordTransformer;
use Exception;
use Illuminate\Support\Collection;

class Application
{
    private string $inputFilePath;

    private TransactionRecordTransformer $transactionRecordTransformer;
    private CommissionCalculator $commissionCalculator;
    private TransactionRepository $transactionRepository;

    public function __construct(
        TransactionRecordTransformer $transactionRecordTransformer,
        CommissionCalculator $commissionCalculator,
        TransactionRepository $transactionRepository,
    ) {
        $this->transactionRecordTransformer = $transactionRecordTransformer;
        $this->commissionCalculator = $commissionCalculator;
        $this->transactionRepository = $transactionRepository;
    }

    public function setInputFilePath(string $inputFilePath): void
    {
        $this->inputFilePath = $inputFilePath;
    }

    /**
     * Entrypoint of the application.
     */
    public function run(): void
    {
        $transactions = collect();
        try {
            $transactions = $this->inputTransactions();
            $transactions = $this->calculateCommissions($transactions);
        } catch (Exception $e) {
            $this->handleException($e);
        }

        $this->outputCommissions($transactions->pluck('commission'));
    }

    private function inputTransactions(): Collection
    {
        return $this->transactionRecordTransformer->parseTransactionsFromCsv($this->inputFilePath);
    }

    private function calculateCommissions(Collection $transactions): Collection
    {
        foreach ($transactions as $transaction) {
            $transaction->commission = $this->commissionCalculator->calculateCommission($transaction);
            $this->transactionRepository->add($transaction);
        }

        return $transactions;
    }

    /**
     * @param Collection<string> $commissions
     */
    private function outputCommissions(Collection $commissions): void
    {
        foreach ($commissions as $commission) {
            echo $commission . PHP_EOL;
        }
    }

    private function handleException(Exception $e): void
    {
        echo $e->getMessage() . PHP_EOL;

        exit('Exiting...' . PHP_EOL);
    }
}
