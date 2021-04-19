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
     * @param \Generated\Shared\Transfer\AvalaraApiLogTransfer $avalaraApiLogTransfer
     *
     * @return void
     */
    public function logAvalaraApiTransaction(AvalaraApiLogTransfer $avalaraApiLogTransfer): void
    {
        $avalaraApiLogTransfer = $this->expandWithCurrentStoreName($avalaraApiLogTransfer);

        $this->avalaraTaxEntityManager->saveTaxAvalaraApiLog($avalaraApiLogTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraApiLogTransfer $avalaraApiLogTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraApiLogTransfer
     */
    protected function expandWithCurrentStoreName(AvalaraApiLogTransfer $avalaraApiLogTransfer): AvalaraApiLogTransfer
    {
        return $avalaraApiLogTransfer->setStoreName($this->storeFacade->getCurrentStore()->getNameOrFail());
    }
}
