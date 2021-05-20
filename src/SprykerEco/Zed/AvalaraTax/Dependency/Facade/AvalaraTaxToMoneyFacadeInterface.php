<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\Facade;

interface AvalaraTaxToMoneyFacadeInterface
{
    /**
     * @param int $value
     *
     * @return float
     */
    public function convertIntegerToDecimal($value);

    /**
     * @param float $value
     *
     * @return int
     */
    public function convertDecimalToInteger($value);
}
