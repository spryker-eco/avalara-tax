<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\StrategyResolver;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\CartItemAvalaraTaxCalculatorInterface;

/**
 * @deprecated Exists for Backward Compatibility reasons only.
 */
interface CartItemTaxCalculatorStrategyResolverInterface
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \SprykerEco\Zed\AvalaraTax\Business\Calculator\CartItemAvalaraTaxCalculatorInterface
     */
    public function resolve(CalculableObjectTransfer $calculableObjectTransfer): CartItemAvalaraTaxCalculatorInterface;
}
