<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

interface AvalaraTaxRepositoryInterface
{
    /**
     * @param int $idProductAbstract
     *
     * @return string|null
     */
    public function findProductAbstractAvalaraTaxCode(int $idProductAbstract): ?string;

    /**
     * @phpstan-return array<string, string>
     *
     * @param string[] $productConcreteSkus
     *
     * @return string[]
     */
    public function getProductConcreteAvalaraTaxCodesBySkus(array $productConcreteSkus): array;

    /**
     * @param string[] $productConcreteSkus
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StockProductTransfer[]
     */
    public function getStockProductsByProductConcreteSkusForStore(array $productConcreteSkus, string $storeName): array;
}
