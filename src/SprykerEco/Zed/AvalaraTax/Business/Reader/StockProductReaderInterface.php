<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Reader;

interface StockProductReaderInterface
{
    /**
     * @param array<string> $skus
     * @param string $storeName
     *
     * @return array<\Generated\Shared\Transfer\StockProductTransfer>
     */
    public function getStockProductsIndexedByProductConcreteSku(array $skus, string $storeName): array;

    /**
     * @param array<string> $stockNames
     * @param string $storeName
     *
     * @return array<\Generated\Shared\Transfer\StockTransfer>
     */
    public function getStocksIndexedByName(array $stockNames, string $storeName): array;
}
