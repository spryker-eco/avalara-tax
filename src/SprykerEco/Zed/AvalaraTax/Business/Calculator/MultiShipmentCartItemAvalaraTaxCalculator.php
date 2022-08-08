<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Calculator;

use ArrayObject;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\AvalaraTransactionLineTransfer;
use Generated\Shared\Transfer\AvalaraTransactionTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapper;

class MultiShipmentCartItemAvalaraTaxCalculator extends AbstractCartItemAvalaraTaxCalculator
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function hasShipmentAddress(CalculableObjectTransfer $calculableObjectTransfer): bool
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
     * @param \ArrayObject<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return void
     */
    protected function calculateTaxForItemTransfers(
        ArrayObject $itemTransfers,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): void {
        $avalaraTransactionTransfer = $avalaraCreateTransactionResponseTransfer->getTransactionOrFail();

        $zipCodeRegionNameMap = $this->getRegionZipCodeMap($avalaraTransactionTransfer);
        foreach ($itemTransfers as $itemTransfer) {
            $this->calculateItemTax(
                $itemTransfer,
                $avalaraTransactionTransfer->getLines(),
                $zipCodeRegionNameMap,
            );
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \ArrayObject<\Generated\Shared\Transfer\AvalaraTransactionLineTransfer> $avalaraTransactionLineTransfers
     * @param array<string> $zipCodeRegionNameMap
     *
     * @return void
     */
    protected function calculateItemTax(
        ItemTransfer $itemTransfer,
        ArrayObject $avalaraTransactionLineTransfers,
        array $zipCodeRegionNameMap
    ): void {
        $avalaraTransactionLineTransfer = $this->findAvalaraLineItemTransferForItemTransfer(
            $itemTransfer,
            $avalaraTransactionLineTransfers,
            $zipCodeRegionNameMap,
        );

        if (!$avalaraTransactionLineTransfer) {
            return;
        }

        $this->calculateItemTaxByAvalaraTransactionLineItem($itemTransfer, $avalaraTransactionLineTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \ArrayObject<\Generated\Shared\Transfer\AvalaraTransactionLineTransfer> $avalaraTransactionLineTransfers
     * @param array<string> $zipCodeRegionNameMap
     *
     * @return \Generated\Shared\Transfer\AvalaraTransactionLineTransfer|null
     */
    protected function findAvalaraLineItemTransferForItemTransfer(
        ItemTransfer $itemTransfer,
        ArrayObject $avalaraTransactionLineTransfers,
        array $zipCodeRegionNameMap
    ): ?AvalaraTransactionLineTransfer {
        foreach ($avalaraTransactionLineTransfers as $avalaraTransactionLineTransfer) {
            if ($avalaraTransactionLineTransfer->getRef1OrFail() !== AvalaraTransactionRequestMapper::CART_ITEM_AVALARA_LINE_TYPE) {
                continue;
            }

            if ($itemTransfer->getGroupKeyOrFail() !== $avalaraTransactionLineTransfer->getRef2OrFail()) {
                continue;
            }

            if (!$avalaraTransactionLineTransfer->getQuantityOrFail()->equals($itemTransfer->getQuantityOrFail())) {
                continue;
            }

            $itemShipmentAddressZipCode = $itemTransfer->getShipmentOrFail()->getShippingAddressOrFail()->getZipCodeOrFail();
            if (
                !isset($zipCodeRegionNameMap[$itemShipmentAddressZipCode])
                || $zipCodeRegionNameMap[$itemShipmentAddressZipCode] !== $this->extractRegionNameFromAvalaraTransactionLineTransfer($avalaraTransactionLineTransfer)
            ) {
                continue;
            }

            return $avalaraTransactionLineTransfer;
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraTransactionTransfer $avalaraTransactionTransfer
     *
     * @return array<string>
     */
    protected function getRegionZipCodeMap(AvalaraTransactionTransfer $avalaraTransactionTransfer): array
    {
        $zipCodeRegionMap = [];

        /** @var array<\Avalara\TransactionAddressModel> $avalaraTransactionAddressModels */
        $avalaraTransactionAddressModels = $this->utilEncodingService->decodeJson($avalaraTransactionTransfer->getAddressesOrFail(), false);
        foreach ($avalaraTransactionAddressModels as $avalaraTransactionAddressModel) {
            if (array_key_exists($avalaraTransactionAddressModel->postalCode, $zipCodeRegionMap)) {
                continue;
            }

            $zipCodeRegionMap[$avalaraTransactionAddressModel->postalCode] = $avalaraTransactionAddressModel->region;
        }

        return $zipCodeRegionMap;
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
     *
     * @return string|null
     */
    protected function extractRegionNameFromAvalaraTransactionLineTransfer(AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer): ?string
    {
        /** @var array<\Avalara\TransactionLineDetailModel> $avalaraTransactionDetailModels */
        $avalaraTransactionDetailModels = $this->utilEncodingService->decodeJson($avalaraTransactionLineTransfer->getDetailsOrFail(), false);
        foreach ($avalaraTransactionDetailModels as $avalaraTransactionDetailModel) {
            if ($avalaraTransactionDetailModel->region === null) {
                continue;
            }

            return $avalaraTransactionDetailModel->region;
        }

        return null;
    }
}
