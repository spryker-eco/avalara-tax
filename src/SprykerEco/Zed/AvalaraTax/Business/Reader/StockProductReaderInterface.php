<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Reader;

interface StockProductReaderInterface
{
    /**
     * @param string[] $skus
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StockProductTransfer[]
     */
    public function getStockProductsIndexedByProductConcreteSku(array $skus, string $storeName): array;

    /**
     * @param string[] $stockNames
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StockTransfer[]
     */
    public function getStocksIndexedByName(array $stockNames, string $storeName): array;
}
