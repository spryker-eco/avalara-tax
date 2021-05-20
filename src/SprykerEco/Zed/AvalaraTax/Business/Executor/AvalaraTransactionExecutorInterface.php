<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;

interface AvalaraTransactionExecutorInterface
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param string $transactionTypeId
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function executeAvalaraCreateTransaction(
        CalculableObjectTransfer $calculableObjectTransfer,
        string $transactionTypeId
    ): AvalaraCreateTransactionResponseTransfer;
}
