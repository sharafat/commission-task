<?php

namespace Tests\Unit\Services;

use App\Services\CurrencyExchangeRateProvider;
use Illuminate\Support\Collection;

class CurrencyExchangeRateProviderStub extends CurrencyExchangeRateProvider
{
    public function getExchangeRates(): Collection
    {
        return collect(
            [
                'base' => 'EUR',
                'rates' => [
                    'USD' => 1.1497,
                    'JPY' => 129.53,
                ],
            ]
        );
    }
}
