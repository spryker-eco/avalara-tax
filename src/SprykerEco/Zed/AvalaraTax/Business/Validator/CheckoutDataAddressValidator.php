<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Validator;

use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
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

        if (!$checkoutDataTransfer->getShippingAddress()) {
            return $checkoutResponseTransfer->setIsSuccess(true);
        }

        $avalaraCreateTransactionResponseTransfer = $this->avalaraResolveAddressExecutor->executeResolveAddressRequest(
            $checkoutDataTransfer->getShippingAddressOrFail()
        );

        if ($avalaraCreateTransactionResponseTransfer->getIsSuccessful()) {
            return $checkoutResponseTransfer->setIsSuccess(true);
        }

        foreach ($avalaraCreateTransactionResponseTransfer->getMessages() as $messageTransfer) {
            $checkoutResponseTransfer->addError(
                (new CheckoutErrorTransfer())->fromArray($messageTransfer->toArray(), true)
            );
        }

        return $checkoutResponseTransfer->setIsSuccess(false);
    }
}
