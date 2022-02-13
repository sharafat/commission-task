<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;

class TransactionRepository
{
    /**
     * @var Collection<Transaction>
     */
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = collect();
    }

    public function add(Transaction $transaction): void
    {
        $this->transactions->push($transaction);
    }

    /**
     * @param Collection<Collection> $transactions
     */
    public function setData(Collection $transactions): void
    {
        $this->transactions = $transactions;
    }

    /**
     * @return Collection<Transaction>
     */
    public function getAll(): Collection
    {
        return $this->transactions;
    }
}
