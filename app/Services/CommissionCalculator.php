<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Services\CommissionRules\BusinessClientWithdrawalRule;
use App\Services\CommissionRules\CommissionRule;
use App\Services\CommissionRules\DepositRule;
use App\Services\CommissionRules\PrivateClientFreeWithdrawalAmountPerWeekRule;
use App\Services\CommissionRules\PrivateClientFreeWithdrawalCountPerWeekRule;
use App\Services\CommissionRules\PrivateClientWithdrawalRule;
use App\Utils\Math;

class CommissionCalculator
{
    /**
     * The commission calculation rules act like a funnel or list of filters. Whenever a rule understands that its
     * conditions have been met, it can calculate the commission or delegate the task to another filter specified by
     * `nextRuleIfPassed`. If the rule understands that its conditions cannot be met, then it passes the task of
     * calculating commission to another filter specified by `nextRuleIfFailed`.
     *
     * This design pattern helps encapsulate each business rule in separate classes, as well as makes it easy to
     * add/remove/reorder business rules with minimal changes.
     */
    private const RULES_IN_ORDER = [
        [
            'rule'             => DepositRule::class,
            'nextRuleIfPassed' => null,
            'nextRuleIfFailed' => BusinessClientWithdrawalRule::class,
        ],
        [
            'rule'             => BusinessClientWithdrawalRule::class,
            'nextRuleIfPassed' => null,
            'nextRuleIfFailed' => PrivateClientFreeWithdrawalCountPerWeekRule::class,
        ],
        [
            'rule'             => PrivateClientFreeWithdrawalCountPerWeekRule::class,
            'nextRuleIfPassed' => PrivateClientFreeWithdrawalAmountPerWeekRule::class,
            'nextRuleIfFailed' => PrivateClientWithdrawalRule::class,
        ],
        [
            'rule'             => PrivateClientFreeWithdrawalAmountPerWeekRule::class,
            'nextRuleIfPassed' => PrivateClientWithdrawalRule::class,
            'nextRuleIfFailed' => PrivateClientWithdrawalRule::class,
        ],
        [
            'rule'             => PrivateClientWithdrawalRule::class,
            'nextRuleIfPassed' => null,
            'nextRuleIfFailed' => null,
        ],
    ];

    private TransactionRepository $transactionRepository;
    private CurrencyExchanger $currencyExchanger;
    private ?CommissionRule $firstCommissionRule = null;

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

    private function getCommissionAmount(Transaction $transaction): float
    {
        if ($this->firstCommissionRule === null) {
            $this->instantiateCommissionRuleObjects();
        }

        return $this->firstCommissionRule->calculateCommission($transaction);
    }

    private function roundUpToDecimalPlacesOfTransactionCurrency(float $commissionAmount, string $transactionAmount): string
    {
        $precision = Math::decimalDigits($transactionAmount);

        return number_format(Math::roundUp($commissionAmount, $precision), $precision, '.', '');
    }

    private function instantiateCommissionRuleObjects(): void
    {
        $rulesInstances = [];
        for ($i = count(self::RULES_IN_ORDER) - 1; $i >= 0; $i--) {
            $rule = self::RULES_IN_ORDER[$i];
            $ruleClass = $rule['rule'];

            $rulesInstances[$ruleClass] = new $ruleClass(
                $rule['nextRuleIfPassed'] !== null ? $rulesInstances[$rule['nextRuleIfPassed']] : null,
                $rule['nextRuleIfFailed'] !== null ? $rulesInstances[$rule['nextRuleIfFailed']] : null,
                $this->transactionRepository
            );
        }

        $rulesInstances[PrivateClientFreeWithdrawalAmountPerWeekRule::class]->setCurrencyExchanger($this->currencyExchanger);

        $this->firstCommissionRule = $rulesInstances[self::RULES_IN_ORDER[0]['rule']];
    }
}
