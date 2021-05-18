<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Communication\Plugin\Cart;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\CartExtension\Dependency\Plugin\CartOperationPostSavePluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 * @method \SprykerEco\Zed\AvalaraTax\Business\AvalaraTaxFacadeInterface getFacade()
 */
class ItemWarehouseCartOperationPostSavePlugin extends AbstractPlugin implements CartOperationPostSavePluginInterface
{
    /**
     * {@inheritDoc}
     * - Expects `QuoteTransfer.items` to be provided.
     * - Requires `QuoteTransfer.store.name` to be set.
     * - Retrieves prioritized (with max stock quantity) stock from Persistence by concrete products SKUs and store.
     * - Expands `QuoteTransfer.items` with warehouse property.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function postSave(QuoteTransfer $quoteTransfer)
    {
        return $this->getFacade()->expandQuoteItemsWithWarehouse($quoteTransfer);
    }
}
