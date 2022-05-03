<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\StockProductTransfer;
use Orm\Zed\Stock\Persistence\SpyStockProduct;

class StockProductMapper
{
    /**
     * @param array<\Orm\Zed\Stock\Persistence\SpyStockProduct> $stockProductEntities
     *
     * @return array<\Generated\Shared\Transfer\StockProductTransfer>
     */
    public function mapStockProductEntitiesToStockProductTransfers(array $stockProductEntities): array
    {
        $stockProductTransfers = [];
        foreach ($stockProductEntities as $stockProductEntity) {
            $stockProductTransfers[] = $this->mapStockProductEntityToStockProductTransfer(
                $stockProductEntity,
                new StockProductTransfer(),
            );
        }

        return $stockProductTransfers;
    }

    /**
     * @param \Orm\Zed\Stock\Persistence\SpyStockProduct $stockProductEntity
     * @param \Generated\Shared\Transfer\StockProductTransfer $stockProductTransfer
     *
     * @return \Generated\Shared\Transfer\StockProductTransfer
     */
    protected function mapStockProductEntityToStockProductTransfer(
        SpyStockProduct $stockProductEntity,
        StockProductTransfer $stockProductTransfer
    ): StockProductTransfer {
        $stockProductTransfer->fromArray($stockProductEntity->toArray(), true);

        $stockProductTransfer
            ->setSku($stockProductEntity->getSpyProduct()->getSku())
            ->setStockType($stockProductEntity->getStock()->getName());

        return $stockProductTransfer;
    }
}
