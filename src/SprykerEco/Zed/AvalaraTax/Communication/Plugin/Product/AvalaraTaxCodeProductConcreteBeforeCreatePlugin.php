<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductConcreteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Product\Dependency\Plugin\ProductConcretePluginCreateInterface;

/**
 * @method \SprykerEco\Zed\AvalaraTax\Business\AvalaraTaxFacadeInterface getFacade()
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 */
class AvalaraTaxCodeProductConcreteBeforeCreatePlugin extends AbstractPlugin implements ProductConcretePluginCreateInterface
{
    /**
     * {@inheritDoc}
     * - Expands product concrete with avalara tax code.
     * - Requires `ProductConcreteTransfer.fkProductAbstract` to be provided.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function create(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer
    {
        return $this->getFacade()->expandProductConcreteTransferWithAvalaraTaxCode($productConcreteTransfer);
    }
}
