<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RuntimeException;

class CurrencyExchanger
{
    private CurrencyExchangeRateProvider $currencyExchangeRateProvider;

    private ?string $baseCurrency = null;
    private Collection $exchangeRates;

    public function __construct(CurrencyExchangeRateProvider $currencyExchangeRateProvider)
    {
        $this->currencyExchangeRateProvider = $currencyExchangeRateProvider;
    }

    public function setCurrencyExchangeRateProvider(CurrencyExchangeRateProvider $currencyExchangeRateProvider): void
    {
        $this->currencyExchangeRateProvider = $currencyExchangeRateProvider;
    }

    public function exchange(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($this->baseCurrency === null) {
            $exchangeRates = $this->currencyExchangeRateProvider->getExchangeRates();

            $this->baseCurrency = $exchangeRates->get('base');
            $this->exchangeRates = collect($exchangeRates->get('rates'));
        }

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        if ($fromCurrency !== $this->baseCurrency && !$this->exchangeRates->has($fromCurrency)) {
            throw new RuntimeException("Unable to get currency exchange rate for $fromCurrency.");
        }
        if ($toCurrency !== $this->baseCurrency && !$this->exchangeRates->has($toCurrency)) {
            throw new RuntimeException("Unable to get currency exchange rate for $toCurrency.");
        }

        if ($fromCurrency === $this->baseCurrency) {
            return $this->exchangeRates[$toCurrency] * $amount;
        }

        if ($toCurrency === $this->baseCurrency) {
            return 1 / $this->exchangeRates[$fromCurrency] * $amount;
        }

        // Convert amount to base currency first, then convert to target currency.
        $amountInBaseCurrency = $this->exchange($amount, $fromCurrency, $this->baseCurrency);

        return $this->exchange($amountInBaseCurrency, $this->baseCurrency, $toCurrency);
    }
}
