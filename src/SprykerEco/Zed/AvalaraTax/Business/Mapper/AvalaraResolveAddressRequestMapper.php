<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AvalaraAddressValidationInfoTransfer;
use Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer;

class AvalaraResolveAddressRequestMapper implements AvalaraResolveAddressRequestMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\AddressTransfer[] $addressTransfers
     * @param \Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer $avalaraResolveAddressRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer
     */
    public function mapAddressTransfersToAvalaraResolveAddressRequestTransfer(
        array $addressTransfers,
        AvalaraResolveAddressRequestTransfer $avalaraResolveAddressRequestTransfer
    ): AvalaraResolveAddressRequestTransfer {
        foreach ($addressTransfers as $addressTransfer) {
            $avalaraAddressValidationInfoTransfer = $this->mapAddressTransferToAvalaraAddressValidationInfoTransfer($addressTransfer, new AvalaraAddressValidationInfoTransfer());

            $avalaraResolveAddressRequestTransfer->addAddress($avalaraAddressValidationInfoTransfer);
        }

        return $avalaraResolveAddressRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     * @param \Generated\Shared\Transfer\AvalaraAddressValidationInfoTransfer $avalaraAddressValidationInfoTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraAddressValidationInfoTransfer
     */
    protected function mapAddressTransferToAvalaraAddressValidationInfoTransfer(
        AddressTransfer $addressTransfer,
        AvalaraAddressValidationInfoTransfer $avalaraAddressValidationInfoTransfer
    ): AvalaraAddressValidationInfoTransfer {
        $avalaraAddressValidationInfoTransfer = $avalaraAddressValidationInfoTransfer->fromArray($addressTransfer->toArray(), true);
        $avalaraAddressValidationInfoTransfer
            ->setLine1($addressTransfer->getAddress1())
            ->setLine2($addressTransfer->getAddress2())
            ->setLine3($addressTransfer->getAddress3())
            ->setPostalCode($addressTransfer->getZipCode());

        if ($addressTransfer->getCountry()) {
            $avalaraAddressValidationInfoTransfer->setCountry($addressTransfer->getCountryOrFail()->getIso2Code());
        }

        return $avalaraAddressValidationInfoTransfer;
    }
}
