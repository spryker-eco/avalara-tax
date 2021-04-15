<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\External;

use stdClass;

interface AvalaraTaxToAvalaraTaxClientInterface
{
    /**
     * @param int $accountId
     * @param string $licenseKey
     *
     * @return $this
     */
    public function withLicenseKey(int $accountId, string $licenseKey);

    /**
     * @param bool $catchExceptions
     *
     * @return $this
     */
    public function withCatchExceptions(bool $catchExceptions = true);

    /**
     * @param \stdClass|\Avalara\CreateTransactionModel $createTransactionModel
     * @param string|null $include
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function createTransaction(stdClass $createTransactionModel, ?string $include = null): stdClass;
}
