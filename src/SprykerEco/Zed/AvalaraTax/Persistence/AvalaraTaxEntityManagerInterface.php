<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

use Generated\Shared\Transfer\AvalaraApiLogTransfer;

interface AvalaraTaxEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\AvalaraApiLogTransfer $avalaraApiLogTransfer
     *
     * @return void
     */
    public function saveTaxAvalaraApiLog(AvalaraApiLogTransfer $avalaraApiLogTransfer): void;
}
