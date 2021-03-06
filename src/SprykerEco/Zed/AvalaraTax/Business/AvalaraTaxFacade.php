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
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerEco\Zed\AvalaraTax\Business\AvalaraTaxBusinessFactory getFactory()
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface getEntityManager()
 */
class AvalaraTaxFacade extends AbstractFacade implements AvalaraTaxFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @throws \Exception
     *
     * @return void
     */
    public function calculateTax(CalculableObjectTransfer $calculableObjectTransfer): void
    {
        $this->getFactory()
            ->createProductItemTaxRateCalculatorStrategyResolver()
            ->resolve($calculableObjectTransfer)
            ->calculateTax($calculableObjectTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function expandProductConcreteWithAvalaraTaxCode(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer
    {
        return $this->getFactory()
            ->createAvalaraTaxCodeExpander()
            ->expandProductConcreteWithAvalaraTaxCode($productConcreteTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function expandCartItemsWithAvalaraTaxCode(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        return $this->getFactory()
            ->createAvalaraTaxCodeExpander()
            ->expandCartItemsWithAvalaraTaxCode($cartChangeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    public function validateCheckoutDataShippingAddress(CheckoutDataTransfer $checkoutDataTransfer): CheckoutResponseTransfer
    {
        return $this->getFactory()
            ->createCheckoutDataAddressValidator()
            ->validateCheckoutDataShippingAddress($checkoutDataTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return bool
     */
    public function isQuoteTaxCalculationValid(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        return $this->getFactory()
            ->createAvalaraTaxQuoteChecker()
            ->isQuoteTaxCalculationValid($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function expandQuoteItemsWithWarehouse(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        return $this->getFactory()
            ->createWarehouseExpander()
            ->expandQuoteItemsWithWarehouse($quoteTransfer);
    }
}
