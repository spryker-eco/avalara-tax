<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\AvalaraApiLogTransfer;
use Orm\Zed\AvalaraTax\Persistence\SpyTaxAvalaraApiLog;

class TaxAvalaraLogApiMapper
{
    /**
     * @param \Generated\Shared\Transfer\AvalaraApiLogTransfer $avalaraApiLogTransfer
     * @param \Orm\Zed\AvalaraTax\Persistence\SpyTaxAvalaraApiLog $taxAvalaraApiLogEntity
     *
     * @return \Orm\Zed\AvalaraTax\Persistence\SpyTaxAvalaraApiLog
     */
    public function mapAvalaraApiLogTransferToTaxAvalaraApiLogEntity(
        AvalaraApiLogTransfer $avalaraApiLogTransfer,
        SpyTaxAvalaraApiLog $taxAvalaraApiLogEntity
    ): SpyTaxAvalaraApiLog {
        $taxAvalaraApiLogEntity->fromArray($avalaraApiLogTransfer->toArray());

        return $taxAvalaraApiLogEntity;
    }
}
