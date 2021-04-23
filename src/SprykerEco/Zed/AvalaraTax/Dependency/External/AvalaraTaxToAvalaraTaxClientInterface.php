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
     * @param string|null $include
     * @param array $createTransactionModel
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function createTransaction(?string $include, array $createTransactionModel): stdClass;
}
