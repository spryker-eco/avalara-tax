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
     * @param \stdClass|\Avalara\CreateTransactionModel $createTransactionModel
     * @param string|null $include
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function createTransaction(stdClass $createTransactionModel, ?string $include = null): stdClass;
}
