<?php

namespace App\Utils;

use App\Models\Transaction;
use Exception;
use Illuminate\Support\Collection;
use RuntimeException;

class TransactionRecordTransformer
{
    /**
     * @param string $csvFilePath
     *
     * @return Collection<Transaction>
     */
    public function parseTransactionsFromCsv(string $csvFilePath): Collection
    {
        try {
            $inputFile = fopen($csvFilePath, 'rb');
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        $transactions = collect();

        while (($row = fgetcsv($inputFile)) !== false) {
            [
                $date,
                $clientId,
                $clientType,
                $operationType,
                $amount,
                $currency,
            ] = $row;

            $transactions->push(new Transaction($date, (int) $clientId, $clientType, $operationType, $amount, $currency));
        }

        fclose($inputFile);

        return $transactions;
    }
}
