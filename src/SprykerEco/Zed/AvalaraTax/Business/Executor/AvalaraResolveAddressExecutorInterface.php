<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;

interface AvalaraResolveAddressExecutorInterface
{
    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function executeResolveAddressRequest(AddressTransfer $addressTransfer): AvalaraCreateTransactionResponseTransfer;
}
