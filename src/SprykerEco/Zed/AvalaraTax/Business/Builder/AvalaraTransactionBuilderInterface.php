<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Builder;

use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface;

interface AvalaraTransactionBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     * @param string $transactionTypeId
     *
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    public function buildCreateTransaction(
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer,
        string $transactionTypeId
    ): AvalaraTaxToTransactionBuilderInterface;
}
