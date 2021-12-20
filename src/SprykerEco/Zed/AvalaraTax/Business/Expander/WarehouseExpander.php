<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Expander;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Reader\StockProductReaderInterface;

class WarehouseExpander implements WarehouseExpanderInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Reader\StockProductReaderInterface
     */
    protected $stockProductReader;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Reader\StockProductReaderInterface $stockProductReader
     */
    public function __construct(StockProductReaderInterface $stockProductReader)
    {
        $this->stockProductReader = $stockProductReader;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function expandQuoteItemsWithWarehouse(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        if (!$quoteTransfer->getItems()->count()) {
            return $quoteTransfer;
        }

        $storeName = $quoteTransfer->getStoreOrFail()->getNameOrFail();
        $indexedStockProductTransfers = $this->stockProductReader->getStockProductsIndexedByProductConcreteSku(
            $this->extractItemSkus($quoteTransfer),
            $storeName,
        );

        $indexedStockTransfers = $this->stockProductReader->getStocksIndexedByName(
            $this->extractStockNames($indexedStockProductTransfers),
            $storeName,
        );

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $itemTransfer = $this->expandQuoteItemWithWarehouse($itemTransfer, $indexedStockProductTransfers, $indexedStockTransfers);
        }

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param array<\Generated\Shared\Transfer\StockProductTransfer> $stockProductTransfers
     * @param array<\Generated\Shared\Transfer\StockTransfer> $stockTransfers
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function expandQuoteItemWithWarehouse(
        ItemTransfer $itemTransfer,
        array $stockProductTransfers,
        array $stockTransfers
    ): ItemTransfer {
        $stockProductTransfer = $stockProductTransfers[$itemTransfer->getSkuOrFail()] ?? null;

        if (!$stockProductTransfer) {
            return $itemTransfer;
        }

        $stockTransfer = $stockTransfers[$stockProductTransfer->getStockTypeOrFail()] ?? null;

        if (!$stockTransfer) {
            return $itemTransfer;
        }

        return $itemTransfer->setWarehouse($stockTransfer);
    }

    /**
     * @param array<\Generated\Shared\Transfer\StockProductTransfer> $stockProductTransfers
     *
     * @return array<string>
     */
    protected function extractStockNames(array $stockProductTransfers): array
    {
        $stockNames = [];

        foreach ($stockProductTransfers as $stockProductTransfer) {
            $stockNames[] = $stockProductTransfer->getStockTypeOrFail();
        }

        return array_unique($stockNames);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<string>
     */
    protected function extractItemSkus(QuoteTransfer $quoteTransfer): array
    {
        $skus = [];

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $skus[] = $itemTransfer->getSkuOrFail();
        }

        return array_unique($skus);
    }
}
