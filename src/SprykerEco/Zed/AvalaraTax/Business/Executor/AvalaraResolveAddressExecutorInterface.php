<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer;

interface AvalaraResolveAddressExecutorInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $addressTransfers
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer
     */
    public function executeResolveAddressRequest(array $addressTransfers): AvalaraResolveAddressResponseTransfer;
}
