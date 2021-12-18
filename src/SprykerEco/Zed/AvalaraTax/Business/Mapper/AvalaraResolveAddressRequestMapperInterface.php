<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer;

interface AvalaraResolveAddressRequestMapperInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $addressTransfers
     * @param \Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer $avalaraResolveAddressRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer
     */
    public function mapAddressTransfersToAvalaraResolveAddressRequestTransfer(
        array $addressTransfers,
        AvalaraResolveAddressRequestTransfer $avalaraResolveAddressRequestTransfer
    ): AvalaraResolveAddressRequestTransfer;
}
