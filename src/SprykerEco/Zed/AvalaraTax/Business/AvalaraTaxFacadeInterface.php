<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;

interface AvalaraTaxFacadeInterface
{
    /**
     * Specification:
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
     * @throws \Exception
     *
     * @return void
     */
    public function calculateTax(CalculableObjectTransfer $calculableObjectTransfer): void;

    /**
     * Specification:
     * - Expands product concrete with avalara tax code.
     * - Requires `ProductConcreteTransfer.fkProductAbstract` to be provided.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function expandProductConcreteWithAvalaraTaxCode(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer;

    /**
     * Specification:
     * - Expands `CartChangeTransfer.items` with avalara tax code.
     * - Requires `CartChangeTransfer.items.sku` to be provided.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function expandCartItemsWithAvalaraTaxCode(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer;

    /**
     * Specification:
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
    public function validateCheckoutDataShippingAddress(CheckoutDataTransfer $checkoutDataTransfer): CheckoutResponseTransfer;
}
