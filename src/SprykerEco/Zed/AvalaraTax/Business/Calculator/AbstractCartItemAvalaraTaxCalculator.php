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
use SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;

abstract class AbstractCartItemAvalaraTaxCalculator implements CartItemAvalaraTaxCalculatorInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface
     */
    protected $avalaraTransactionExecutor;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig
     */
    protected $avalaraTaxConfig;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @var array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestAfterPluginInterface>
     */
    protected $createTransactionRequestAfterPlugins;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface $avalaraTransactionExecutor
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface $moneyFacade
     * @param \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig $avalaraTaxConfig
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     * @param array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestAfterPluginInterface> $createTransactionRequestAfterPlugins
     */
    public function __construct(
        AvalaraTransactionExecutorInterface $avalaraTransactionExecutor,
        AvalaraTaxToMoneyFacadeInterface $moneyFacade,
        AvalaraTaxConfig $avalaraTaxConfig,
        AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService,
        array $createTransactionRequestAfterPlugins
    ) {
        $this->avalaraTransactionExecutor = $avalaraTransactionExecutor;
        $this->moneyFacade = $moneyFacade;
        $this->avalaraTaxConfig = $avalaraTaxConfig;
        $this->utilEncodingService = $utilEncodingService;
        $this->createTransactionRequestAfterPlugins = $createTransactionRequestAfterPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function calculateTax(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        if (!$this->isCalculationApplicable($calculableObjectTransfer)) {
            return;
        }

        $avalaraCreateTransactionResponseTransfer = $this->avalaraTransactionExecutor->executeAvalaraCreateTransaction(
            $calculableObjectTransfer,
            (string)$this->resolveAvalaraTransactionType($calculableObjectTransfer),
        );

        $this->setAvalaraCreateTransactionResponseToOriginalQuote($calculableObjectTransfer, $avalaraCreateTransactionResponseTransfer);

        if (!$avalaraCreateTransactionResponseTransfer->getIsSuccessful()) {
            return;
        }

        $this->calculateTaxForItemTransfers($calculableObjectTransfer->getItems(), $avalaraCreateTransactionResponseTransfer);

        $this->executeCreateTransactionRequestAfterPlugins($calculableObjectTransfer, $avalaraCreateTransactionResponseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function isCalculationApplicable(CalculableObjectTransfer $calculableObjectTransfer): bool
    {
        if (!$calculableObjectTransfer->getItems()->count()) {
            return false;
        }

        return $this->hasShipmentAddress($calculableObjectTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return int
     */
    protected function resolveAvalaraTransactionType(CalculableObjectTransfer $calculableObjectTransfer): int
    {
        if ($calculableObjectTransfer->getOriginalQuote() && $calculableObjectTransfer->getOriginalQuoteOrFail()->getOrderReference()) {
            return $this->avalaraTaxConfig->getAfterOrderPlacedTransactionTypeId();
        }

        return $this->avalaraTaxConfig->getBeforeOrderPlacedTransactionTypeId();
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    abstract protected function hasShipmentAddress(CalculableObjectTransfer $calculableObjectTransfer): bool;

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
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
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return void
     */
    protected function executeCreateTransactionRequestAfterPlugins(
        CalculableObjectTransfer $calculableObjectTransfer,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): void {
        foreach ($this->createTransactionRequestAfterPlugins as $createTransactionRequestAfterPlugin) {
            $calculableObjectTransfer = $createTransactionRequestAfterPlugin->execute($calculableObjectTransfer, $avalaraCreateTransactionResponseTransfer);
        }
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
        $taxRate = $this->calculateAvalaraTransactionLineTaxRate($avalaraTransactionLineTransfer->getDetailsOrFail());
        $taxAmount = $this->moneyFacade->convertDecimalToInteger($avalaraTransactionLineTransfer->getTaxOrFail()->toFloat());

        $itemTransfer
            ->setTaxRate($taxRate)
            ->setSumTaxAmount($taxAmount);
    }

    /**
     * @param string $transactionLineDetails
     *
     * @return float
     */
    protected function calculateAvalaraTransactionLineTaxRate(string $transactionLineDetails): float
    {
        $taxRateSum = 0.0;

        /** @var array<\Avalara\TransactionLineDetailModel> $transactionLineDetailsDecoded */
        $transactionLineDetailsDecoded = $this->utilEncodingService->decodeJson($transactionLineDetails, false);
        foreach ($transactionLineDetailsDecoded as $transactionLineDetail) {
            $taxRateSum += $transactionLineDetail->rate ?? 0.0;
        }

        return $this->convertToPercents($taxRateSum);
    }

    /**
     * @param float $number
     *
     * @return float
     */
    protected function convertToPercents(float $number): float
    {
        return $number * 100.0;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return void
     */
    protected function setAvalaraCreateTransactionResponseToOriginalQuote(
        CalculableObjectTransfer $calculableObjectTransfer,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): void {
        if (!$calculableObjectTransfer->getOriginalQuote()) {
            return;
        }

        $calculableObjectTransfer->getOriginalQuoteOrFail()
            ->setAvalaraCreateTransactionResponse($avalaraCreateTransactionResponseTransfer);
    }
}
