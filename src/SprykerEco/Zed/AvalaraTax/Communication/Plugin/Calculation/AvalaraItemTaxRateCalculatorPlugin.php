<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Communication\Plugin\Calculation;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Spryker\Zed\CalculationExtension\Dependency\Plugin\CalculationPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 * @method \SprykerEco\Zed\AvalaraTax\Business\AvalaraTaxFacadeInterface getFacade()
 */
class AvalaraItemTaxRateCalculatorPlugin extends AbstractPlugin implements CalculationPluginInterface
{
    /**
     * {@inheritDoc}
     * - Calculate taxes based on the response data received from Avalara Tax API.
     * - Executes `CreateTransactionRequestExpanderPluginInterface` plugin stack to expand request before it's sent.
     * - Sends a `createTransaction` request to Avalara Tax API.
     * - In case of failure stops further plugin stack execution and logs the exceptions.
     * - Executes `CreateTransactionRequestAfterPluginInterface` plugin stack after successful response.
     * - Sets the received taxes to taxation objects.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer)
    {
        $this->getFacade()->calculateTax($calculableObjectTransfer);
    }
}
