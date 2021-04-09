<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business;

use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
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
    public function expandProductConcreteTransferWithAvalaraTaxCode(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer
    {
        return $this->getFactory()
            ->createAvalaraTaxSetExpander()
            ->expandProductConcreteTransferWithAvalaraTaxCode($productConcreteTransfer);
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
    public function expandItemTransfersWithAvalaraTaxCode(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        return $this->getFactory()
            ->createAvalaraTaxSetExpander()
            ->expandCartItemTransfersWithAvalaraTaxCode($cartChangeTransfer);
    }
}