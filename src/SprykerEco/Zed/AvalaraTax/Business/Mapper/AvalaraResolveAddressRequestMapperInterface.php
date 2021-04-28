<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer;

interface AvalaraResolveAddressRequestMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     * @param \Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer $avalaraResolveAddressRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer
     */
    public function mapAddressTransferToAvalaraResolveAddressRequestTransfer(
        AddressTransfer $addressTransfer,
        AvalaraResolveAddressRequestTransfer $avalaraResolveAddressRequestTransfer
    ): AvalaraResolveAddressRequestTransfer;
}
