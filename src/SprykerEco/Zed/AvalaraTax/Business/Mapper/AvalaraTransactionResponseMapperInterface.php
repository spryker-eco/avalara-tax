<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use stdClass;

interface AvalaraTransactionResponseMapperInterface
{
    /**
     * @param \stdClass|\Avalara\TransactionModel $transactionModel
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function mapAvalaraTransactionModelToAvalaraTransactionTransfer(
        stdClass $transactionModel,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): AvalaraCreateTransactionResponseTransfer;
}
