<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Checker;

use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToCalculationFacadeInterface;

class AvalaraTaxQuoteChecker implements AvalaraTaxQuoteCheckerInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToCalculationFacadeInterface
     */
    protected $calculationFacade;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToCalculationFacadeInterface $calculationFacade
     */
    public function __construct(AvalaraTaxToCalculationFacadeInterface $calculationFacade)
    {
        $this->calculationFacade = $calculationFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return bool
     */
    public function isQuoteTaxCalculationValid(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        $quoteTransfer = $this->calculationFacade->recalculateQuote($quoteTransfer);

        if ($quoteTransfer->getAvalaraCreateTransactionResponseOrFail()->getIsSuccessful()) {
            return true;
        }

        foreach ($quoteTransfer->getAvalaraCreateTransactionResponseOrFail()->getMessages() as $messageTransfer) {
            $checkoutResponseTransfer->addError($this->createCheckoutErrorTransfer($messageTransfer));
        }

        $checkoutResponseTransfer->setIsSuccess(false);

        return false;
    }

    /**
     * @param \Generated\Shared\Transfer\MessageTransfer $messageTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutErrorTransfer
     */
    protected function createCheckoutErrorTransfer(MessageTransfer $messageTransfer): CheckoutErrorTransfer
    {
        return (new CheckoutErrorTransfer())->fromArray($messageTransfer->toArray(), true);
    }
}
