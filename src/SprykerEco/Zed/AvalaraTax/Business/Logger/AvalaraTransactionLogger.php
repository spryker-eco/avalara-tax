<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Logger;

use Generated\Shared\Transfer\AvalaraApiLogTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface;

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
     * @param \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface $avalaraTaxEntityManager
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        AvalaraTaxEntityManagerInterface $avalaraTaxEntityManager,
        AvalaraTaxToStoreFacadeInterface $storeFacade
    ) {
        $this->avalaraTaxEntityManager = $avalaraTaxEntityManager;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param array $logData
     *
     * @return void
     */
    public function logAvalaraApiTransaction(array $logData): void
    {
        $avalaraApiLogTransfer = $this->createAvalaraApiLogTransfer($logData);

        $this->avalaraTaxEntityManager->saveTaxAvalaraApiLog($avalaraApiLogTransfer);
    }

    /**
     * @param array $logData
     *
     * @return \Generated\Shared\Transfer\AvalaraApiLogTransfer
     */
    protected function createAvalaraApiLogTransfer(array $logData): AvalaraApiLogTransfer
    {
        return (new AvalaraApiLogTransfer())
            ->fromArray($logData, true)
            ->setStoreName($this->storeFacade->getCurrentStore()->getNameOrFail());
    }
}
