<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Generated\Shared\Transfer\AvalaraApiLogTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;
use Throwable;

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
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface $avalaraTransactionBuilder
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface $avalaraTransactionRequestMapper
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface $avalaraTransactionResponseMapper
     * @param \SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface $avalaraTransactionLogger
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        AvalaraTransactionBuilderInterface $avalaraTransactionBuilder,
        AvalaraTransactionRequestMapperInterface $avalaraTransactionRequestMapper,
        AvalaraTransactionResponseMapperInterface $avalaraTransactionResponseMapper,
        AvalaraTransactionLoggerInterface $avalaraTransactionLogger,
        AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->avalaraTransactionBuilder = $avalaraTransactionBuilder;
        $this->avalaraTransactionRequestMapper = $avalaraTransactionRequestMapper;
        $this->avalaraTransactionResponseMapper = $avalaraTransactionResponseMapper;
        $this->avalaraTransactionLogger = $avalaraTransactionLogger;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param string $transactionTypeId
     *
     * @throws \Exception
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function executeAvalaraCreateTransaction(
        CalculableObjectTransfer $calculableObjectTransfer,
        string $transactionTypeId
    ): AvalaraCreateTransactionResponseTransfer {
        $avalaraCreateTransactionRequestTransfer = $this->avalaraTransactionRequestMapper
            ->mapCalculableObjectTransferToAvalaraCreateTransactionRequestTransfer(
                $calculableObjectTransfer,
                new AvalaraCreateTransactionRequestTransfer()
            );

        $transactionBuilder = $this->avalaraTransactionBuilder->buildCreateTransaction(
            $avalaraCreateTransactionRequestTransfer,
            $transactionTypeId,
        );

        $avalaraApiLogTransfer = (new AvalaraApiLogTransfer())
            ->setRequest($this->utilEncodingService->encodeJson($transactionBuilder->toArray()))
            ->setTransactionType($transactionTypeId);

        try {
            $transactionModel = $transactionBuilder->create();

            $avalaraApiLogTransfer
                ->setIsSuccessful(true)
                ->setResponse($this->utilEncodingService->encodeJson((array)$transactionModel));
            file_put_contents(APPLICATION_ROOT_DIR . '/response-5.json', json_encode($transactionModel, JSON_PRETTY_PRINT));
        } catch (Throwable $e) {
            $avalaraApiLogTransfer
                ->setIsSuccessful(false)
                ->setErrorMessage($e->getMessage());

            throw $e;
        } finally {
            $this->avalaraTransactionLogger->logAvalaraApiTransaction($avalaraApiLogTransfer);
        }

        $avalaraCreateTransactionResponseTransfer = (new AvalaraCreateTransactionResponseTransfer())
            ->setIsSuccessful(true);

        return $this->avalaraTransactionResponseMapper->mapAvalaraTransactionModelToAvalaraCreateTransactionResponseTransfer(
            $transactionModel,
            $avalaraCreateTransactionResponseTransfer
        );
    }
}
