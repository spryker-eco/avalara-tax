<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Calculator;

use ArrayObject;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\AvalaraTransactionLineTransfer;
use Generated\Shared\Transfer\AvalaraTransactionTransfer;
use Generated\Shared\Transfer\ItemTransfer;

class MultiShipmentCartItemAvalaraTaxCalculator extends AbstractCartItemAvalaraTaxCalculator
{
    protected const KEY_REGION = 'region';
    protected const KEY_POSTAL_CODE = 'postalCode';

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
        $avalaraTransactionTransfer = $avalaraCreateTransactionResponseTransfer->getTransactionOrFail();

        $zipCodeRegionNameMap = $this->getRegionZipCodeMap($avalaraTransactionTransfer);
        foreach ($itemTransfers as $itemTransfer) {
            $this->calculateItemTax(
                $itemTransfer,
                $avalaraTransactionTransfer->getLines(),
                $zipCodeRegionNameMap
            );
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \ArrayObject|\Generated\Shared\Transfer\AvalaraTransactionLineTransfer[] $avalaraTransactionLineTransfers
     * @param array $zipCodeRegionNameMap
     *
     * @return void
     */
    protected function calculateItemTax(
        ItemTransfer $itemTransfer,
        ArrayObject $avalaraTransactionLineTransfers,
        array $zipCodeRegionNameMap
    ): void {
        foreach ($avalaraTransactionLineTransfers as $avalaraTransactionLineTransfer) {
            if ($itemTransfer->getGroupKeyOrFail() !== $avalaraTransactionLineTransfer->getRef2OrFail()) {
                continue;
            }

            $avalaraTransactionLineTransfer = $this->findAvalaraLineItemTransferForItemTransfer($itemTransfer, $avalaraTransactionLineTransfers, $zipCodeRegionNameMap);
            if (!$avalaraTransactionLineTransfer) {
                continue;
            }

            $this->calculateItemTaxByAvalaraTransactionLineItem($itemTransfer, $avalaraTransactionLineTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \ArrayObject|\Generated\Shared\Transfer\AvalaraTransactionLineTransfer[] $avalaraLineItemTransfers
     * @param array $zipCodeRegionNameMap
     *
     * @return \Generated\Shared\Transfer\AvalaraTransactionLineTransfer|null
     */
    protected function findAvalaraLineItemTransferForItemTransfer(
        ItemTransfer $itemTransfer,
        ArrayObject $avalaraLineItemTransfers,
        array $zipCodeRegionNameMap
    ): ?AvalaraTransactionLineTransfer {
        foreach ($avalaraLineItemTransfers as $avalaraLineItemTransfer) {
            if (!$itemTransfer->getAmountOrFail()->equals($avalaraLineItemTransfer->getQuantityOrFail())) {
                continue;
            }

            $itemShipmentAddressZipCode = $itemTransfer->getShipmentOrFail()->getShippingAddressOrFail()->getZipCodeOrFail();
            if (
                !isset($zipCodeRegionNameMap[$itemShipmentAddressZipCode])
                || $zipCodeRegionNameMap[$itemShipmentAddressZipCode] !== $this->extractRegionNameFromAvalaraTransactionLineTransfer($avalaraLineItemTransfer)
            ) {
                continue;
            }

            return $avalaraLineItemTransfer;
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraTransactionTransfer $avalaraTransactionTransfer
     *
     * @return array
     */
    protected function getRegionZipCodeMap(AvalaraTransactionTransfer $avalaraTransactionTransfer): array
    {
        $zipCodeRegionMap = [];

        $avalaraTransactionAddresses = $this->utilEncodingService->decodeJson($avalaraTransactionTransfer->getAddressesOrFail(), true);
        foreach ($avalaraTransactionAddresses as $avalaraTransactionAddress) {
            if (array_key_exists($avalaraTransactionAddress[static::KEY_POSTAL_CODE], $zipCodeRegionMap)) {
                continue;
            }

            $zipCodeRegionMap[$avalaraTransactionAddress[static::KEY_POSTAL_CODE]] = $avalaraTransactionAddress[static::KEY_REGION];
        }

        return $zipCodeRegionMap;
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\AvalaraTransactionLineTransfer[] $avalaraTransactionLineTransfers
     *
     * @return \Generated\Shared\Transfer\AvalaraTransactionLineTransfer[][]
     */
    protected function getAvalaraTransactionLineTransfersIndexedByGroupKey(ArrayObject $avalaraTransactionLineTransfers): array
    {
        $indexedAvalaraTransactionLineTransfers = [];
        foreach ($avalaraTransactionLineTransfers as $avalaraTransactionLineTransfer) {
            $indexedAvalaraTransactionLineTransfers[$avalaraTransactionLineTransfer->getRef2OrFail()][] = $avalaraTransactionLineTransfer;
        }

        return $indexedAvalaraTransactionLineTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
     *
     * @return string|null
     */
    protected function extractRegionNameFromAvalaraTransactionLineTransfer(AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer): ?string
    {
        $avalaraTransactionDetails = $this->utilEncodingService->decodeJson($avalaraTransactionLineTransfer->getDetailsOrFail(), true);
        foreach ($avalaraTransactionDetails as $avalaraTransactionDetail) {
            if (!isset($avalaraTransactionDetail[static::KEY_REGION])) {
                continue;
            }

            return $avalaraTransactionDetail[static::KEY_REGION];
        }

        return null;
    }
}
