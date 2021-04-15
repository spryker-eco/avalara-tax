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
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @var \SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestAfterPluginInterface[]
     */
    protected $createTransactionRequestAfterPlugins;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface $avalaraTransactionExecutor
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface $moneyFacade
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     * @param \SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestAfterPluginInterface[] $createTransactionRequestAfterPlugins
     */
    public function __construct(
        AvalaraTransactionExecutorInterface $avalaraTransactionExecutor,
        AvalaraTaxToMoneyFacadeInterface $moneyFacade,
        AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService,
        array $createTransactionRequestAfterPlugins
    ) {
        $this->avalaraTransactionExecutor = $avalaraTransactionExecutor;
        $this->moneyFacade = $moneyFacade;
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
        if (!$this->hasShipmentAddress($calculableObjectTransfer)) {
            return;
        }

        $avalaraCreateTransactionResponseTransfer = $this->avalaraTransactionExecutor->executeAvalaraCreateTransaction(
            $calculableObjectTransfer,
            (string)AvalaraTaxConfig::AVALARA_TRANSACTION_TYPE_ID_SALES_ORDER
        );

        $calculableObjectTransfer->getOriginalQuoteOrFail()->setAvalaraCreateTransactionResponse($avalaraCreateTransactionResponseTransfer);
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
    abstract protected function hasShipmentAddress(CalculableObjectTransfer $calculableObjectTransfer): bool;

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

        /** @var \Avalara\TransactionLineDetailModel[] $transactionLineDetailsDecoded */
        $transactionLineDetailsDecoded = $this->utilEncodingService->decodeJson($transactionLineDetails, false);
        foreach ($transactionLineDetailsDecoded as $transactionLineDetail) {
            $taxRateSum += $transactionLineDetail->rate ?? 0.0;
        }

        return $taxRateSum;
    }
}
