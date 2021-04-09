<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Calculator;

use ArrayObject;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\AvalaraTransactionLineTransfer;

class SingleShipmentCartItemAvalaraTaxCalculator extends AbstractCartItemAvalaraTaxCalculator
{
    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return void
     */
    protected function calculateTaxForItemTransfers(
        ArrayObject $itemTransfers,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): void {
        $avalaraTransactionLineTransfersIndexedByGroupKey = $this->getAvalaraTransactionLineTransfersIndexedByGroupKey(
            $avalaraCreateTransactionResponseTransfer->getTransactionOrFail()->getLines()
        );

        foreach ($avalaraTransactionLineTransfersIndexedByGroupKey as $groupKey => $avalaraTransactionLineTransfer) {
            $this->calculateItemsTaxByGroupKey($itemTransfers, $groupKey, $avalaraTransactionLineTransfer);
        }
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     * @param string $groupKey
     * @param \Generated\Shared\Transfer\AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
     *
     * @return void
     */
    protected function calculateItemsTaxByGroupKey(
        ArrayObject $itemTransfers,
        string $groupKey,
        AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
    ): void {
        foreach ($itemTransfers as $itemTransfer) {
            if ($itemTransfer->getGroupKeyOrFail() !== $groupKey) {
                continue;
            }

            $this->calculateItemTaxByAvalaraTransactionLineItem($itemTransfer, $avalaraTransactionLineTransfer);
        }
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\AvalaraTransactionLineTransfer[] $avalaraTransactionLineTransfers
     *
     * @return \Generated\Shared\Transfer\AvalaraTransactionLineTransfer[]
     */
    protected function getAvalaraTransactionLineTransfersIndexedByGroupKey(ArrayObject $avalaraTransactionLineTransfers): array
    {
        $indexedAvalaraTransactionLineTransfers = [];
        foreach ($avalaraTransactionLineTransfers as $avalaraTransactionLineTransfer) {
            $indexedAvalaraTransactionLineTransfers[$avalaraTransactionLineTransfer->getRef2OrFail()] = $avalaraTransactionLineTransfer;
        }

        return $indexedAvalaraTransactionLineTransfers;
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
