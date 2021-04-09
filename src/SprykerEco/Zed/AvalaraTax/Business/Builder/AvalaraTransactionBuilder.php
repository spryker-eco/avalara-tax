<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Builder;

use Avalara\TransactionAddressType;
use Avalara\TransactionBuilder;
use Generated\Shared\Transfer\AvalaraAddressTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use Generated\Shared\Transfer\AvalaraLineItemTransfer;

class AvalaraTransactionBuilder implements AvalaraTransactionBuilderInterface
{
    /**
     * @var \Avalara\TransactionBuilder
     */
    protected $transactionBuilder;

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     */
    public function __construct(TransactionBuilder $transactionBuilder)
    {
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     * @param int $transactionType
     *
     * @return \Avalara\TransactionBuilder
     */
    public function buildCreateTransaction(
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer,
        int $transactionType
    ): TransactionBuilder {
        $avalaraCreateTransactionTransfer = $avalaraCreateTransactionRequestTransfer->getTransactionOrFail();

        $transactionBuilder = $this->transactionBuilder
            ->withType($transactionType)
            ->withCurrencyCode($avalaraCreateTransactionTransfer->getCurrencyCodeOrFail());

        if ($avalaraCreateTransactionTransfer->getShippingAddress()) {
            $transactionBuilder = $this->addTransactionLevelShippingAddress(
                $transactionBuilder,
                $avalaraCreateTransactionRequestTransfer->getTransactionOrFail()->getShippingAddressOrFail()
            );
        }

        foreach ($avalaraCreateTransactionTransfer->getLines() as $avalaraLineItemTransfer) {
            $transactionBuilder = $this->addItem($transactionBuilder, $avalaraLineItemTransfer);
        }

        return $transactionBuilder;
    }

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param \Generated\Shared\Transfer\AvalaraAddressTransfer $avalaraAddressTransfer
     *
     * @return \Avalara\TransactionBuilder
     */
    protected function addTransactionLevelShippingAddress(
        TransactionBuilder $transactionBuilder,
        AvalaraAddressTransfer $avalaraAddressTransfer
    ): TransactionBuilder {
        $addressTransfer = $avalaraAddressTransfer->getAddressOrFail();

        return $transactionBuilder->withAddress(
            TransactionAddressType::C_SHIPTO,
            $addressTransfer->getAddress1(),
            $addressTransfer->getAddress2(),
            $addressTransfer->getAddress3(),
            $addressTransfer->getCity(),
            $addressTransfer->getRegion(),
            $addressTransfer->getZipCode(),
            $addressTransfer->getIso2Code()
        );
    }

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param \Generated\Shared\Transfer\AvalaraLineItemTransfer $avalaraLineItemTransfer
     *
     * @return \Avalara\TransactionBuilder
     */
    protected function addItem(
        TransactionBuilder $transactionBuilder,
        AvalaraLineItemTransfer $avalaraLineItemTransfer
    ): TransactionBuilder {
        $transactionBuilder->withLine(
            $avalaraLineItemTransfer->getAmountOrFail()->toFloat(),
            $avalaraLineItemTransfer->getQuantityOrFail(),
            $avalaraLineItemTransfer->getItemCodeOrFail(),
            $avalaraLineItemTransfer->getTaxCodeOrFail()
        );
        $transactionBuilder->withLineCustomFields(
            $avalaraLineItemTransfer->getReference1OrFail(),
            $avalaraLineItemTransfer->getReference2()
        );
        $transactionBuilder->withLineDescription($avalaraLineItemTransfer->getDescriptionOrFail());

        if ($avalaraLineItemTransfer->getTaxIncluded()) {
            $transactionBuilder->withLineTaxIncluded();
        }

        if ($avalaraLineItemTransfer->getShippingAddress() === null) {
            return $transactionBuilder;
        }

        return $this->addItemShipmentAddress($transactionBuilder, $avalaraLineItemTransfer);
    }

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param \Generated\Shared\Transfer\AvalaraLineItemTransfer $avalaraLineItemTransfer
     *
     * @return \Avalara\TransactionBuilder
     */
    protected function addItemShipmentAddress(
        TransactionBuilder $transactionBuilder,
        AvalaraLineItemTransfer $avalaraLineItemTransfer
    ): TransactionBuilder {
        $addressTransfer = $avalaraLineItemTransfer->getShippingAddressOrFail()->getAddressOrFail();

        return $transactionBuilder->withLineAddress(
            TransactionAddressType::C_SHIPTO,
            $addressTransfer->getAddress1(),
            $addressTransfer->getAddress2(),
            $addressTransfer->getAddress3(),
            $addressTransfer->getCity(),
            $addressTransfer->getRegion(),
            $addressTransfer->getZipCode(),
            $addressTransfer->getIso2Code()
        );
    }
}
