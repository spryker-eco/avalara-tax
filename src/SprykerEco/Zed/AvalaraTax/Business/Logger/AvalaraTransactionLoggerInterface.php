<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Logger;

interface AvalaraTransactionLoggerInterface
{
    /**
     * @param array $logData
     *
     * @return void
     */
    public function logAvalaraApiTransaction(array $logData): void;
}
