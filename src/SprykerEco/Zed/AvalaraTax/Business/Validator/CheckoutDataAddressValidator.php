<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Validator;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraResolveAddressExecutorInterface;

class CheckoutDataAddressValidator implements CheckoutDataAddressValidatorInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraResolveAddressExecutorInterface
     */
    protected $avalaraResolveAddressExecutor;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraResolveAddressExecutorInterface $avalaraResolveAddressExecutor
     */
    public function __construct(AvalaraResolveAddressExecutorInterface $avalaraResolveAddressExecutor)
    {
        $this->avalaraResolveAddressExecutor = $avalaraResolveAddressExecutor;
    }

    /**
     * @param \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    public function validateCheckoutDataShippingAddress(CheckoutDataTransfer $checkoutDataTransfer): CheckoutResponseTransfer
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        if (!$checkoutDataTransfer->getShippingAddress() && $checkoutDataTransfer->getShipments()->count() === 0) {
            return $checkoutResponseTransfer->setIsSuccess(true);
        }

        $avalaraResolveAddressResponseTransfer = $this->avalaraResolveAddressExecutor->executeResolveAddressRequest(
            $this->extractShippingAddressesFromCheckoutDataTransfer($checkoutDataTransfer),
        );

        if ($avalaraResolveAddressResponseTransfer->getIsSuccessful()) {
            return $checkoutResponseTransfer->setIsSuccess(true);
        }

        foreach ($avalaraResolveAddressResponseTransfer->getMessages() as $messageTransfer) {
            $checkoutResponseTransfer->addError(
                (new CheckoutErrorTransfer())->fromArray($messageTransfer->toArray(), true),
            );
        }

        return $checkoutResponseTransfer->setIsSuccess(false);
    }

    /**
     * @param \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer
     *
     * @return array<\Generated\Shared\Transfer\AddressTransfer>
     */
    protected function extractShippingAddressesFromCheckoutDataTransfer(CheckoutDataTransfer $checkoutDataTransfer): array
    {
        $addressTransfers = [];
        if ($checkoutDataTransfer->getShippingAddress()) {
            $addressTransfer = $checkoutDataTransfer->getShippingAddressOrFail();
            $addressTransfer->setCountry((new CountryTransfer())->setIso2Code($addressTransfer->getIso2CodeOrFail()));

            $addressTransfers[] = $addressTransfer;

            return $addressTransfers;
        }

        if ($checkoutDataTransfer->getShipments()->count() === 0) {
            return $addressTransfers;
        }

        foreach ($checkoutDataTransfer->getShipments() as $restShipmentsTransfer) {
            if (!$restShipmentsTransfer->getShippingAddress()) {
                continue;
            }

            $restAddressTransfer = $restShipmentsTransfer->getShippingAddressOrFail();
            $addressTransfers[] = (new AddressTransfer())
                ->fromArray($restAddressTransfer->toArray(), true)
                ->setCountry((new CountryTransfer())->setIso2Code($restAddressTransfer->getIso2CodeOrFail()));
        }

        return $addressTransfers;
    }
}
