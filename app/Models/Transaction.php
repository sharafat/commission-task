<?php

namespace App\Models;

use Carbon\Carbon;

class Transaction
{
    public const BUSINESS_CLIENT = 'business';
    public const PRIVATE_CLIENT = 'private';

    public const DEPOSIT_OPERATION = 'deposit';
    public const WITHDRAW_OPERATION = 'withdraw';

    public Carbon $date;
    public int $clientId;
    public string $clientType;
    public string $operationType;
    public string $amount;
    public string $currency;
    public ?string $commission;

    public function __construct(
        string $date,
        int $clientId,
        string $clientType,
        string $operationType,
        string $amount,
        string $currency,
        ?string $commission = null
    ) {
        $this->setDate($date);
        $this->clientId = $clientId;
        $this->clientType = $clientType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->commission = $commission;
    }

    public function setDate(string $date): void
    {
        $this->date = Carbon::parse($date)->setTime(0, 0);
    }
}
