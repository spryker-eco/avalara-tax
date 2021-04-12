<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

use Generated\Shared\Transfer\AvalaraApiLogTransfer;
use Orm\Zed\AvalaraTax\Persistence\SpyTaxAvalaraApiLog;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxPersistenceFactory getFactory()
 */
class AvalaraTaxEntityManager extends AbstractEntityManager implements AvalaraTaxEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\AvalaraApiLogTransfer $avalaraApiLogTransfer
     *
     * @return void
     */
    public function saveTaxAvalaraApiLog(AvalaraApiLogTransfer $avalaraApiLogTransfer): void
    {
        $taxAvalaraLogApiEntity = $this->getFactory()
            ->createTaxAvalaraLogApiMapper()
            ->mapAvalaraApiLogTransferToTaxAvalaraApiLogEntity($avalaraApiLogTransfer, new SpyTaxAvalaraApiLog());

        $taxAvalaraLogApiEntity->save();
    }
}
