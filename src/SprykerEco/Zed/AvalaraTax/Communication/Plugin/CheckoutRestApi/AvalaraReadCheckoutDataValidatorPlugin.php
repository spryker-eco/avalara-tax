<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Communication\Plugin\CheckoutRestApi;

use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Spryker\Zed\CheckoutRestApiExtension\Dependency\Plugin\ReadCheckoutDataValidatorPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 * @method \SprykerEco\Zed\AvalaraTax\Business\AvalaraTaxFacadeInterface getFacade()
 */
class AvalaraReadCheckoutDataValidatorPlugin extends AbstractPlugin implements ReadCheckoutDataValidatorPluginInterface
{
    /**
     * {@inheritDoc}
     * - Validates `CheckoutDataTransfer.shippingAddress` if it is set.
     * - Sends request to Avalara address resolve endpoint.
     * - Maps response from avalara to `CheckoutResponseTransfer`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    public function validateCheckoutData(CheckoutDataTransfer $checkoutDataTransfer): CheckoutResponseTransfer
    {
        return $this->getFacade()->validateCheckoutDataShippingAddress($checkoutDataTransfer);
    }
}
