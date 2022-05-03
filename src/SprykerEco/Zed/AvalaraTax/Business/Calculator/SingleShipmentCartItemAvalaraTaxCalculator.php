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
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapper;

/**
 * @deprecated Exists for Backward Compatibility reasons only.
 */
class SingleShipmentCartItemAvalaraTaxCalculator extends AbstractCartItemAvalaraTaxCalculator
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function hasShipmentAddress(CalculableObjectTransfer $calculableObjectTransfer): bool
    {
        return $calculableObjectTransfer->getShippingAddress() && $calculableObjectTransfer->getShippingAddressOrFail()->getZipCode();
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return void
     */
    protected function calculateTaxForItemTransfers(
        ArrayObject $itemTransfers,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): void {
        $avalaraTransactionLineTransfersIndexedByGroupKey = $this->getCartItemAvalaraTransactionLineTransfersIndexedByGroupKey(
            $avalaraCreateTransactionResponseTransfer->getTransactionOrFail()->getLines()
        );

        foreach ($avalaraTransactionLineTransfersIndexedByGroupKey as $groupKey => $avalaraTransactionLineTransfer) {
            $this->calculateItemsTaxByGroupKey($itemTransfers, $groupKey, $avalaraTransactionLineTransfer);
        }
    }

    /**
     * @param \ArrayObject<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
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
     * @param \ArrayObject<\Generated\Shared\Transfer\AvalaraTransactionLineTransfer> $avalaraTransactionLineTransfers
     *
     * @return \Generated\Shared\Transfer\AvalaraTransactionLineTransfer[]
     */
    protected function getCartItemAvalaraTransactionLineTransfersIndexedByGroupKey(ArrayObject $avalaraTransactionLineTransfers): array
    {
        $indexedAvalaraTransactionLineTransfers = [];
        foreach ($avalaraTransactionLineTransfers as $avalaraTransactionLineTransfer) {
            if ($avalaraTransactionLineTransfer->getRef1OrFail() !== AvalaraTransactionRequestMapper::CART_ITEM_AVALARA_LINE_TYPE) {
                continue;
            }
            $indexedAvalaraTransactionLineTransfers[$avalaraTransactionLineTransfer->getRef2OrFail()] = $avalaraTransactionLineTransfer;
        }

        return $indexedAvalaraTransactionLineTransfers;
    }
}
