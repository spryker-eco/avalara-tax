<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Avalara\DocumentType;
use Exception;
use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface;

class AvalaraTransactionExecutor implements AvalaraTransactionExecutorInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface
     */
    protected $avalaraTransactionBuilder;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface
     */
    protected $avalaraTransactionRequestMapper;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface
     */
    protected $avalaraTransactionResponseMapper;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface
     */
    protected $avalaraTransactionLogger;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface $avalaraTransactionBuilder
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface $avalaraTransactionRequestMapper
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface $avalaraTransactionResponseMapper
     * @param \SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface $avalaraTransactionLogger
     */
    public function __construct(
        AvalaraTransactionBuilderInterface $avalaraTransactionBuilder,
        AvalaraTransactionRequestMapperInterface $avalaraTransactionRequestMapper,
        AvalaraTransactionResponseMapperInterface $avalaraTransactionResponseMapper,
        AvalaraTransactionLoggerInterface $avalaraTransactionLogger
    ) {
        $this->avalaraTransactionBuilder = $avalaraTransactionBuilder;
        $this->avalaraTransactionRequestMapper = $avalaraTransactionRequestMapper;
        $this->avalaraTransactionResponseMapper = $avalaraTransactionResponseMapper;
        $this->avalaraTransactionLogger = $avalaraTransactionLogger;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @throws \Exception
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function executeAvalaraSalesOrderTransaction(
        CalculableObjectTransfer $calculableObjectTransfer
    ): AvalaraCreateTransactionResponseTransfer {
        $avalaraCreateTransactionRequestTransfer = $this->avalaraTransactionRequestMapper
            ->mapCalculableObjectTransferToAvalaraCreateTransactionRequestTransfer(
                $calculableObjectTransfer,
                new AvalaraCreateTransactionRequestTransfer()
            );

        $transactionBuilder = $this->avalaraTransactionBuilder->buildCreateTransaction(
            $avalaraCreateTransactionRequestTransfer,
            DocumentType::C_SALESORDER,
        );

        try {
            /** @var \stdClass $transactionModel */
            $transactionModel = $transactionBuilder->create();

            $this->avalaraTransactionLogger->logSuccessfulAvalaraApiTransaction(
                $transactionBuilder,
                (string)DocumentType::C_SALESORDER,
                $transactionModel
            );
        } catch (Exception $e) {
            $this->avalaraTransactionLogger->logFailedAvalaraApiTransaction(
                $transactionBuilder,
                (string)DocumentType::C_SALESORDER,
                $e->getMessage()
            );

            throw $e;
        }

        $avalaraCreateTransactionResponseTransfer = (new AvalaraCreateTransactionResponseTransfer())
            ->setIsSuccessful(true);

        return $this->avalaraTransactionResponseMapper->mapAvalaraTransactionModelToAvalaraTransactionTransfer(
            $transactionModel,
            $avalaraCreateTransactionResponseTransfer
        );
    }
}
