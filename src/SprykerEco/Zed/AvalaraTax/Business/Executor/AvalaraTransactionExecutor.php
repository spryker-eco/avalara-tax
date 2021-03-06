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
use Generated\Shared\Transfer\MessageTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;
use SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface;
use stdClass;
use Throwable;

class AvalaraTransactionExecutor implements AvalaraTransactionExecutorInterface
{
    /**
     * @var array<\Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer>
     */
    protected static $avalaraCreateTransactionResponseCache;

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
     * @var \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface
     */
    protected $avalaraTaxEntityManager;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface $avalaraTransactionBuilder
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface $avalaraTransactionRequestMapper
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface $avalaraTransactionResponseMapper
     * @param \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface $avalaraTaxEntityManager
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        AvalaraTransactionBuilderInterface $avalaraTransactionBuilder,
        AvalaraTransactionRequestMapperInterface $avalaraTransactionRequestMapper,
        AvalaraTransactionResponseMapperInterface $avalaraTransactionResponseMapper,
        AvalaraTaxEntityManagerInterface $avalaraTaxEntityManager,
        AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->avalaraTransactionBuilder = $avalaraTransactionBuilder;
        $this->avalaraTransactionRequestMapper = $avalaraTransactionRequestMapper;
        $this->avalaraTransactionResponseMapper = $avalaraTransactionResponseMapper;
        $this->avalaraTaxEntityManager = $avalaraTaxEntityManager;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param string $transactionTypeId
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function executeAvalaraCreateTransaction(
        CalculableObjectTransfer $calculableObjectTransfer,
        string $transactionTypeId
    ): AvalaraCreateTransactionResponseTransfer {
        $cacheKey = $this->generateCacheKey($calculableObjectTransfer);

        if (isset(static::$avalaraCreateTransactionResponseCache[$cacheKey])) {
            return static::$avalaraCreateTransactionResponseCache[$cacheKey];
        }

        $avalaraCreateTransactionRequestTransfer = $this->avalaraTransactionRequestMapper
            ->mapCalculableObjectTransferToAvalaraCreateTransactionRequestTransfer(
                $calculableObjectTransfer,
                new AvalaraCreateTransactionRequestTransfer(),
            );

        $transactionBuilder = $this->avalaraTransactionBuilder->buildCreateTransaction(
            $avalaraCreateTransactionRequestTransfer,
            $transactionTypeId,
        );

        $avalaraApiLogTransfer = (new AvalaraApiLogTransfer())
            ->setStoreName($calculableObjectTransfer->getStoreOrFail()->getNameOrFail())
            ->setRequest($this->utilEncodingService->encodeJson($transactionBuilder->toArray()))
            ->setTransactionType($transactionTypeId);

        try {
            $transactionModel = $transactionBuilder->create();

            $avalaraApiLogTransfer
                ->setIsSuccessful(true)
                ->setResponse($this->utilEncodingService->encodeJson((array)$transactionModel));

            $avalaraCreateTransactionResponseTransfer = $this->buildAvalaraCreateTransactionResponse($transactionModel, $cacheKey);
        } catch (Throwable $e) {
            $avalaraApiLogTransfer
                ->setIsSuccessful(false)
                ->setErrorMessage($e->getMessage());

            $avalaraCreateTransactionResponseTransfer = (new AvalaraCreateTransactionResponseTransfer())
                ->setIsSuccessful(false)
                ->addMessage((new MessageTransfer())->setValue($avalaraApiLogTransfer->getErrorMessage()));
        } finally {
            $this->avalaraTaxEntityManager->saveTaxAvalaraApiLog($avalaraApiLogTransfer);
        }

        return $avalaraCreateTransactionResponseTransfer;
    }

    /**
     * @param \stdClass|\Avalara\TransactionModel $transactionModel
     * @param string $cacheKey
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    protected function buildAvalaraCreateTransactionResponse(
        stdClass $transactionModel,
        string $cacheKey
    ): AvalaraCreateTransactionResponseTransfer {
        $avalaraCreateTransactionResponseTransfer = (new AvalaraCreateTransactionResponseTransfer())
            ->setIsSuccessful(true);

        static::$avalaraCreateTransactionResponseCache[$cacheKey] = $this->avalaraTransactionResponseMapper
            ->mapAvalaraTransactionModelToAvalaraCreateTransactionResponseTransfer(
                $transactionModel,
                $avalaraCreateTransactionResponseTransfer,
            );

        return static::$avalaraCreateTransactionResponseCache[$cacheKey];
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return string
     */
    protected function generateCacheKey(CalculableObjectTransfer $calculableObjectTransfer): string
    {
        $key = serialize($calculableObjectTransfer->toArray());

        return md5($key);
    }
}
