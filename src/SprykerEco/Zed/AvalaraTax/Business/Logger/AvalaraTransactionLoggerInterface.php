<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Logger;

use Generated\Shared\Transfer\AvalaraApiLogTransfer;

interface AvalaraTransactionLoggerInterface
{
    /**
     * @param \Generated\Shared\Transfer\AvalaraApiLogTransfer $avalaraApiLogTransfer
     *
     * @return void
     */
    public function logAvalaraApiTransaction(AvalaraApiLogTransfer $avalaraApiLogTransfer): void;
}
