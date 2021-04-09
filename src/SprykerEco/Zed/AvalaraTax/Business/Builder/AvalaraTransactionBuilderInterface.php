<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Builder;

use Avalara\TransactionBuilder;
use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;

interface AvalaraTransactionBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     * @param int $transactionType
     *
     * @return \Avalara\TransactionBuilder
     */
    public function buildCreateTransaction(
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer,
        int $transactionType
    ): TransactionBuilder;
}
