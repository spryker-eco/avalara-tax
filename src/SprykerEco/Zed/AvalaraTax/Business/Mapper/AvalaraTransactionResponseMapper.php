<?php

/**
 * MIT License
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
    protected const KEY_ADDRESSES = 'addresses';
    protected const KEY_LOCATION_TYPES = 'locationTypes';
    protected const KEY_SUMMARY = 'summary';
    protected const KEY_MESSAGE = 'messages';
    protected const KEY_INVOICE_MESSAGE = 'invoiceMessages';
    protected const KEY_DETAILS = 'details';
    protected const KEY_NON_PASSTHROUGH_DETAILS = 'nonPassthroughDetails';
    protected const KEY_LINE_LOCATION_TYPES = 'lineLocationTypes';
    protected const KEY_PARAMETERS = 'parameters';
    protected const KEY_TAX_AMOUNT_BY_TAX_TYPES = 'taxAmountByTaxTypes';

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
    public function mapAvalaraTransactionModelToAvalaraCreateTransactionResponseTransfer(
        stdClass $transactionModel,
        AvalaraCreateTransactionResponseTransfer $avalaraCreateTransactionResponseTransfer
    ): AvalaraCreateTransactionResponseTransfer {
        $avalaraTransactionTransfer = (new AvalaraTransactionTransfer())->fromArray($this->convertStdClassToArray($transactionModel), true);
        $avalaraTransactionTransfer
            ->setAddresses($this->encodeStdClassPropertyToJson($transactionModel, static::KEY_ADDRESSES))
            ->setLocationTypes($this->encodeStdClassPropertyToJson($transactionModel, static::KEY_LOCATION_TYPES))
            ->setSummary($this->encodeStdClassPropertyToJson($transactionModel, static::KEY_SUMMARY))
            ->setMessages($this->encodeStdClassPropertyToJson($transactionModel, static::KEY_MESSAGE))
            ->setInvoiceMessages($this->encodeStdClassPropertyToJson($transactionModel, static::KEY_INVOICE_MESSAGE));

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
            ->setDetails($this->encodeStdClassPropertyToJson($transactionLineModel, static::KEY_DETAILS))
            ->setNonPassthroughDetails($this->encodeStdClassPropertyToJson($transactionLineModel, static::KEY_NON_PASSTHROUGH_DETAILS))
            ->setLineLocationTypes($this->encodeStdClassPropertyToJson($transactionLineModel, static::KEY_LINE_LOCATION_TYPES))
            ->setParameters($this->encodeStdClassPropertyToJson($transactionLineModel, static::KEY_PARAMETERS))
            ->setTaxAmountByTaxTypes($this->encodeStdClassPropertyToJson($transactionLineModel, static::KEY_TAX_AMOUNT_BY_TAX_TYPES));

        return $avalaraTransactionLineTransfer;
    }

    /**
     * @param \stdClass $stdClass
     *
     * @return array
     */
    protected function convertStdClassToArray(stdClass $stdClass): array
    {
        return (array)$stdClass;
    }

    /**
     * @param \stdClass $stdClass
     * @param string $propertyName
     *
     * @return string|null
     */
    protected function encodeStdClassPropertyToJson(stdClass $stdClass, string $propertyName): ?string
    {
        $propertyData = property_exists($stdClass, $propertyName) ? $stdClass->$propertyName : [];

        return $this->utilEncodingService->encodeJson($propertyData);
    }
}
