<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\AvalaraTransactionLineTransfer;
use Generated\Shared\Transfer\AvalaraTransactionTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;
use stdClass;

class AvalaraTransactionResponseMapper implements AvalaraTransactionResponseMapperInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService)
    {
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \stdClass|\Avalara\TransactionModel $transactionModel
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function mapAvalaraTransactionModelToAvalaraTransactionTransfer(
        stdClass $transactionModel,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): AvalaraCreateTransactionResponseTransfer {
        $avalaraTransactionTransfer = (new AvalaraTransactionTransfer())->fromArray($this->convertStdClassToArray($transactionModel), true);
        $avalaraTransactionTransfer
            ->setAddresses($this->encodeStdClassPropertyToJson($transactionModel, 'addresses'))
            ->setLocationTypes($this->encodeStdClassPropertyToJson($transactionModel, 'locationTypes'))
            ->setSummary($this->encodeStdClassPropertyToJson($transactionModel, 'summary'))
            ->setMessages($this->encodeStdClassPropertyToJson($transactionModel, 'messages'))
            ->setInvoiceMessages($this->encodeStdClassPropertyToJson($transactionModel, 'invoiceMessages'));

        $avalaraTransactionLineTransfers = [];
        foreach ($transactionModel->lines as $transactionLineModel) {
            $avalaraTransactionLineTransfers[] = $this->mapTransactionLineModelToAvalaraTransactionLineTransfer(
                $transactionLineModel,
                new AvalaraTransactionLineTransfer()
            );
        }
        $avalaraTransactionTransfer->setLines(new ArrayObject($avalaraTransactionLineTransfers));

        return $avalaraCreateTransactionResponseTransfer->setTransaction($avalaraTransactionTransfer);
    }

    /**
     * @param \stdClass|\Avalara\TransactionLineModel $transactionLineModel
     * @param \Generated\Shared\Transfer\AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraTransactionLineTransfer
     */
    protected function mapTransactionLineModelToAvalaraTransactionLineTransfer(
        stdClass $transactionLineModel,
        AvalaraTransactionLineTransfer $avalaraTransactionLineTransfer
    ): AvalaraTransactionLineTransfer {
        $transactionLineModelData = $this->convertStdClassToArray($transactionLineModel);

        $avalaraTransactionLineTransfer = $avalaraTransactionLineTransfer->fromArray($transactionLineModelData, true);
        $avalaraTransactionLineTransfer
            ->setDetails($this->encodeStdClassPropertyToJson($transactionLineModel, 'details'))
            ->setNonPassthroughDetails($this->encodeStdClassPropertyToJson($transactionLineModel, 'nonPassthroughDetails'))
            ->setLineLocationTypes($this->encodeStdClassPropertyToJson($transactionLineModel, 'lineLocationTypes'))
            ->setParameters($this->encodeStdClassPropertyToJson($transactionLineModel, 'parameters'))
            ->setTaxAmountByTaxTypes($this->encodeStdClassPropertyToJson($transactionLineModel, 'taxAmountByTaxTypes'));

        return $avalaraTransactionLineTransfer;
    }

    /**
     * @param \stdClass $stdClass
     *
     * @return array
     */
    protected function convertStdClassToArray(stdClass $stdClass): array
    {
        $stdClassEncoded = $this->utilEncodingService->encodeJson((array)$stdClass);
        if (!$stdClassEncoded) {
            return [];
        }

        return $this->utilEncodingService->decodeJson($stdClassEncoded, true);
    }

    /**
     * @param \stdClass $stdClass
     * @param string $propertyName
     *
     * @return string|null
     */
    protected function encodeStdClassPropertyToJson(stdClass $stdClass, string $propertyName): ?string
    {
        return $this->utilEncodingService->encodeJson(
            property_exists($stdClass, $propertyName)
                ? $stdClass->$propertyName
                : []
        );
    }
}
