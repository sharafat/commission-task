<?php

namespace App\Services;

use Exception;
use Http;
use Illuminate\Support\Collection;
use RuntimeException;

class CurrencyExchangeRateProvider
{
    private Collection $rateData;

    private const EXCHANGE_RATE_API_ENDPOINT = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';

    public function __construct()
    {
        $this->rateData = collect();
    }

    public function getExchangeRates(): Collection
    {
        if ($this->rateData->isEmpty()) {
            $this->rateData = collect($this->retrieveExchangeRatesFromApi());
        }

        return $this->rateData;
    }

    private function retrieveExchangeRatesFromApi(): array
    {
        try {
            return Http::get(self::EXCHANGE_RATE_API_ENDPOINT)->throw()->json();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
