<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Calculator;

use Generated\Shared\Transfer\CalculableObjectTransfer;

interface CartItemAvalaraTaxCalculatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function calculateTax(CalculableObjectTransfer $calculableObjectTransfer): void;
}
