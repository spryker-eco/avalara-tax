<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Builder;

use Generated\Shared\Transfer\AvalaraAddressTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use Generated\Shared\Transfer\AvalaraLineItemTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface;

class AvalaraTransactionBuilder implements AvalaraTransactionBuilderInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface $transactionBuilder
     */
    public function __construct(AvalaraTaxToTransactionBuilderInterface $transactionBuilder)
    {
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     * @param string $transactionTypeId
     *
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    public function buildCreateTransaction(
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer,
        string $transactionTypeId
    ): AvalaraTaxToTransactionBuilderInterface {
        $avalaraCreateTransactionTransfer = $avalaraCreateTransactionRequestTransfer->getTransactionOrFail();

        $transactionBuilder = $this->transactionBuilder
            ->withType($transactionTypeId)
            ->withCurrencyCode($avalaraCreateTransactionTransfer->getCurrencyCodeOrFail());

        if ($avalaraCreateTransactionTransfer->getWithCommit()) {
            $transactionBuilder->withCommit();
        }

        if ($avalaraCreateTransactionTransfer->getPurchaseOrderNo()) {
            $transactionBuilder->withPurchaseOrderNo($avalaraCreateTransactionTransfer->getPurchaseOrderNoOrFail());
        }

        if ($avalaraCreateTransactionTransfer->getShippingAddress()) {
            $transactionBuilder = $this->addTransactionLevelAddress(
                $transactionBuilder,
                $avalaraCreateTransactionTransfer->getShippingAddressOrFail()
            );
        }

        foreach ($avalaraCreateTransactionTransfer->getLines() as $avalaraLineItemTransfer) {
            $transactionBuilder = $this->addItem($transactionBuilder, $avalaraLineItemTransfer);
        }

        return $transactionBuilder;
    }

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface $transactionBuilder
     * @param \Generated\Shared\Transfer\AvalaraAddressTransfer $avalaraAddressTransfer
     *
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    protected function addTransactionLevelAddress(
        AvalaraTaxToTransactionBuilderInterface $transactionBuilder,
        AvalaraAddressTransfer $avalaraAddressTransfer
    ): AvalaraTaxToTransactionBuilderInterface {
        $addressTransfer = $avalaraAddressTransfer->getAddressOrFail();

        return $transactionBuilder->withAddress(
            $avalaraAddressTransfer->getTypeOrFail(),
            $addressTransfer->getAddress1OrFail(),
            $addressTransfer->getAddress2OrFail(),
            $addressTransfer->getAddress3() ?? '',
            $addressTransfer->getCityOrFail(),
            $addressTransfer->getZipCodeOrFail(),
            $addressTransfer->getIso2CodeOrFail(),
            $addressTransfer->getRegion()
        );
    }

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface $transactionBuilder
     * @param \Generated\Shared\Transfer\AvalaraLineItemTransfer $avalaraLineItemTransfer
     *
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    protected function addItem(
        AvalaraTaxToTransactionBuilderInterface $transactionBuilder,
        AvalaraLineItemTransfer $avalaraLineItemTransfer
    ): AvalaraTaxToTransactionBuilderInterface {
        $transactionBuilder->withLine(
            $avalaraLineItemTransfer->getAmountOrFail()->toFloat(),
            $avalaraLineItemTransfer->getQuantityOrFail(),
            $avalaraLineItemTransfer->getItemCodeOrFail(),
            $avalaraLineItemTransfer->getTaxCodeOrFail()
        );
        $transactionBuilder->withLineCustomFields(
            $avalaraLineItemTransfer->getReference1OrFail(),
            $avalaraLineItemTransfer->getReference2OrFail()
        );

        if ($avalaraLineItemTransfer->getDescription()) {
            $transactionBuilder->withLineDescription($avalaraLineItemTransfer->getDescriptionOrFail());
        }

        if ($avalaraLineItemTransfer->getTaxIncluded()) {
            $transactionBuilder->withLineTaxIncluded();
        }

        if ($avalaraLineItemTransfer->getShippingAddress() !== null) {
            $transactionBuilder = $this->addItemLevelAddress($transactionBuilder, $avalaraLineItemTransfer->getShippingAddressOrFail());
        }

        if ($avalaraLineItemTransfer->getSourceAddress() !== null) {
            $transactionBuilder = $this->addItemLevelAddress($transactionBuilder, $avalaraLineItemTransfer->getSourceAddressOrFail());
        }

        return $transactionBuilder;
    }

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface $transactionBuilder
     * @param \Generated\Shared\Transfer\AvalaraAddressTransfer $avalaraAddressTransfer
     *
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    protected function addItemLevelAddress(
        AvalaraTaxToTransactionBuilderInterface $transactionBuilder,
        AvalaraAddressTransfer $avalaraAddressTransfer
    ): AvalaraTaxToTransactionBuilderInterface {
        $addressTransfer = $avalaraAddressTransfer->getAddressOrFail();

        return $transactionBuilder->withLineAddress(
            $avalaraAddressTransfer->getTypeOrFail(),
            $addressTransfer->getAddress1OrFail(),
            $addressTransfer->getAddress2OrFail(),
            (string)$addressTransfer->getAddress3(),
            $addressTransfer->getCityOrFail(),
            $addressTransfer->getZipCodeOrFail(),
            $addressTransfer->getIso2CodeOrFail(),
            $addressTransfer->getRegion()
        );
    }
}
