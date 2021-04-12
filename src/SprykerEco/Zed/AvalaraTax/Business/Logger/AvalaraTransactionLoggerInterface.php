<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Logger;

use Avalara\TransactionBuilder;
use stdClass;

interface AvalaraTransactionLoggerInterface
{
    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param string $transactionType
     * @param \stdClass|\Avalara\TransactionModel $transactionModel
     *
     * @return void
     */
    public function logSuccessfulAvalaraApiTransaction(TransactionBuilder $transactionBuilder, string $transactionType, stdClass $transactionModel): void;

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param string $transactionType
     * @param string $message
     *
     * @return void
     */
    public function logFailedAvalaraApiTransaction(TransactionBuilder $transactionBuilder, string $transactionType, string $message): void;
}
