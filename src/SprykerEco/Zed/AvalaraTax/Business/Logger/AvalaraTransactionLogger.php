<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Logger;

use Avalara\TransactionBuilder;
use Generated\Shared\Transfer\AvalaraApiLogTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;
use SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface;
use stdClass;

class AvalaraTransactionLogger implements AvalaraTransactionLoggerInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface
     */
    protected $avalaraTaxEntityManager;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface $avalaraTaxEntityManager
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface $storeFacade
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        AvalaraTaxEntityManagerInterface $avalaraTaxEntityManager,
        AvalaraTaxToStoreFacadeInterface $storeFacade,
        AvalaraTaxToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->avalaraTaxEntityManager = $avalaraTaxEntityManager;
        $this->storeFacade = $storeFacade;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param string $transactionType
     * @param \stdClass $transactionModel
     *
     * @return void
     */
    public function logSuccessfulAvalaraApiTransaction(TransactionBuilder $transactionBuilder, string $transactionType, stdClass $transactionModel): void
    {
        $avalaraApiLogTransfer = $this->createAvalaraApiLogTransfer($transactionBuilder, $transactionType);
        $avalaraApiLogTransfer->setResponse($this->utilEncodingService->encodeJson((array)$transactionModel));
        $avalaraApiLogTransfer->setIsSuccessful(true);

        $this->avalaraTaxEntityManager->saveTaxAvalaraApiLog($avalaraApiLogTransfer);
    }

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param string $transactionType
     * @param string $message
     *
     * @return void
     */
    public function logFailedAvalaraApiTransaction(TransactionBuilder $transactionBuilder, string $transactionType, string $message): void
    {
        $avalaraApiLogTransfer = $this->createAvalaraApiLogTransfer($transactionBuilder, $transactionType);
        $avalaraApiLogTransfer->setErrorMessage($message);
        $avalaraApiLogTransfer->setIsSuccessful(false);

        $this->avalaraTaxEntityManager->saveTaxAvalaraApiLog($avalaraApiLogTransfer);
    }

    /**
     * @param \Avalara\TransactionBuilder $transactionBuilder
     * @param string $transactionType
     *
     * @return \Generated\Shared\Transfer\AvalaraApiLogTransfer
     */
    protected function createAvalaraApiLogTransfer(TransactionBuilder $transactionBuilder, string $transactionType): AvalaraApiLogTransfer
    {
        return (new AvalaraApiLogTransfer())
            ->setTransactionType($transactionType)
            ->setStoreName($this->storeFacade->getCurrentStore()->getNameOrFail())
            ->setRequest($this->utilEncodingService->encodeJson((array)$transactionBuilder));
    }
}
