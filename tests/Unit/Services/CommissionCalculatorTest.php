<?php

namespace Tests\Unit\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Services\CommissionCalculator;
use App\Services\CurrencyExchanger;
use Tests\CreatesApplication;
use Tests\TestCase;
use function collect;
use function resolve;

class CommissionCalculatorTest extends TestCase
{
    use CreatesApplication;

    private CommissionCalculator $commissionCalculator;

    public function setUp(): void
    {
        $this->commissionCalculator = resolve(CommissionCalculator::class);
        $this->prepareCurrencyExchangeRateProviderStub();
    }

    /**
     * @dataProvider dataProviderForCommissionCalculationTesting
     */
    public function testCommissionCalculation(int $index, Transaction $transaction): void
    {
        $this->setupDbSoThatCommissionCalculatorCanHaveItReady($index);
        $this->assertEquals($transaction->commission, $this->commissionCalculator->calculateCommission($transaction));
    }

    public function dataProviderForCommissionCalculationTesting(): array
    {
        return [
            [0, new Transaction('2014-12-31', 4, 'private', 'withdraw', '1200.00', 'EUR', '0.60')],
            [1, new Transaction('2015-01-01', 4, 'private', 'withdraw', '1000.00', 'EUR', '3.00')],
            [2, new Transaction('2016-01-05', 4, 'private', 'withdraw', '1000.00', 'EUR', '0.00')],
            [3, new Transaction('2016-01-05', 1, 'private', 'deposit', '200.00', 'EUR', '0.06')],
            [4, new Transaction('2016-01-06', 2, 'business', 'withdraw', '300.00', 'EUR', '1.50')],
            [5, new Transaction('2016-01-06', 1, 'private', 'withdraw', '30000', 'JPY', '0')],
            [6, new Transaction('2016-01-07', 1, 'private', 'withdraw', '1000.00', 'EUR', '0.70')],
            [7, new Transaction('2016-01-07', 1, 'private', 'withdraw', '100.00', 'USD', '0.30')],
            [8, new Transaction('2016-01-10', 1, 'private', 'withdraw', '100.00', 'EUR', '0.30')],
            [9, new Transaction('2016-01-10', 2, 'business', 'deposit', '10000.00', 'EUR', '3.00')],
            [10, new Transaction('2016-01-10', 3, 'private', 'withdraw', '1000.00', 'EUR', '0.00')],
            [11, new Transaction('2016-02-15', 1, 'private', 'withdraw', '300.00', 'EUR', '0.00')],
            [12, new Transaction('2016-02-19', 5, 'private', 'withdraw', '3000000', 'JPY', '8612')],
        ];
    }

    private function prepareCurrencyExchangeRateProviderStub(): void
    {
        $currencyExchanger = resolve(CurrencyExchanger::class);
        $currencyExchanger->setCurrencyExchangeRateProvider(new CurrencyExchangeRateProviderStub());
        $this->commissionCalculator->setCurrencyExchanger($currencyExchanger);
    }

    private function setupDbSoThatCommissionCalculatorCanHaveItReady(int $dataRowIndex): void
    {
        $dataSet = collect($this->dataProviderForCommissionCalculationTesting())
            ->filter(fn(array $row) => $row[0] < $dataRowIndex)    // Take records prior to current dataset row
            ->map(fn(array $row) => $row[1])
            ->values();

        $transactionRepository = resolve(TransactionRepository::class);
        $transactionRepository->setData($dataSet);

        $this->commissionCalculator->setTransactionRepository($transactionRepository);
    }
}
