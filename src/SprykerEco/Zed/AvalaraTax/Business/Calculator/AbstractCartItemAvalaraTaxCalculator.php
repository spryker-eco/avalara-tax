<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Calculator;

use ArrayObject;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\AvalaraTransactionLineTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;

abstract class AbstractCartItemAvalaraTaxCalculator implements CartItemAvalaraTaxCalculatorInterface
{
    protected const KEY_TAX_RATE = 'rate';

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface
     */
    protected $avalaraTransactionExecutor;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface $avalaraTransactionExecutor
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface $moneyFacade
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        AvalaraTransactionExecutorInterface $avalaraTransactionExecutor,
        AvalaraTaxToMoneyFacadeInterface $moneyFacade,
        AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->avalaraTransactionExecutor = $avalaraTransactionExecutor;
        $this->moneyFacade = $moneyFacade;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function calculateTax(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        if (!$this->hasShipmentAddress($calculableObjectTransfer)) {
            return;
        }

        $avalaraCreateTransactionResponseTransfer = $this->avalaraTransactionExecutor->executeAvalaraSalesOrderTransaction(
            $calculableObjectTransfer
        );

        $calculableObjectTransfer->getOriginalQuoteOrFail()->setAvalaraCreateTransactionResponse($avalaraCreateTransactionResponseTransfer);
        if (!$avalaraCreateTransactionResponseTransfer->getIsSuccessful()) {
            return;
        }

        $this->calculateTaxForItemTransfers($calculableObjectTransfer->getItems(), $avalaraCreateTransactionResponseTransfer);
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return void
     */
    abstract protected function calculateTaxForItemTransfers(
        ArrayObject $itemTransfers,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): void;

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function hasShipmentAddress(CalculableObjectTransfer $calculableObjectTransfer): bool
    {
        return ($calculableObjectTransfer->getShippingAddress() && $calculableObjectTransfer->getShippingAddressOrFail()->getZipCode())
            || $this->isMultiAddressShipment($calculableObjectTransfer) !== false;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function isMultiAddressShipment(CalculableObjectTransfer $calculableObjectTransfer): bool
    {
        foreach ($calculableObjectTransfer->getItems() as $itemTransfer) {
            if (
                !$itemTransfer->getShipment()
                || !$itemTransfer->getShipmentOrFail()->getShippingAddress()
                || !$itemTransfer->getShipmentOrFail()->getShippingAddressOrFail()->getZipCode()
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
     *
     * @return void
     */
    protected function calculateItemTaxByAvalaraTransactionLineItem(
        ItemTransfer $itemTransfer,
        AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
    ): void {
        $taxRate = $this->calculateAvalaraTransactionLineTaxRate($avalaraTransactionLineTransfer);
        $taxAmount = $this->moneyFacade->convertDecimalToInteger($avalaraTransactionLineTransfer->getTaxOrFail()->toFloat());

        $itemTransfer
            ->setTaxRate($taxRate)
            ->setSumTaxAmount($taxAmount);
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
     *
     * @return int
     */
    protected function calculateAvalaraTransactionLineTaxRate(AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer): int
    {
        $taxRateSum = $this->sumTaxRateFromTransactionLineDetails($avalaraTransactionLineTransfer->getDetailsOrFail());

        return $this->moneyFacade->convertDecimalToInteger($taxRateSum);
    }

    /**
     * @param string $transactionLineDetails
     *
     * @return float
     */
    protected function sumTaxRateFromTransactionLineDetails(string $transactionLineDetails): float
    {
        $taxRateSum = 0;

        $transactionLineDetailsDecoded = $this->utilEncodingService->decodeJson($transactionLineDetails, true);
        foreach ($transactionLineDetailsDecoded as $transactionLineDetail) {
            $taxRateSum += $transactionLineDetail[static::KEY_TAX_RATE] ?? 0;
        }

        return $taxRateSum;
    }
}
