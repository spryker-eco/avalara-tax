<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Orm\Zed\Stock\Persistence\Map\SpyStockProductTableMap;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxPersistenceFactory getFactory()
 */
class AvalaraTaxRepository extends AbstractRepository implements AvalaraTaxRepositoryInterface
{
    /**
     * @param int $idProductAbstract
     *
     * @return string|null
     */
    public function findProductAbstractAvalaraTaxCode(int $idProductAbstract): ?string
    {
        return $this->getFactory()
            ->getProductAbstractPropelQuery()
            ->filterByIdProductAbstract($idProductAbstract)
            ->select(SpyProductAbstractTableMap::COL_AVALARA_TAX_CODE)
            ->findOne();
    }

    /**
     * @phpstan-return array<string, string>
     *
     * @param array<string> $productConcreteSkus
     *
     * @return array<string>
     */
    public function getProductConcreteAvalaraTaxCodesBySkus(array $productConcreteSkus): array
    {
        return $this->getFactory()
            ->getProductPropelQuery()
            ->filterBySku_In($productConcreteSkus)
            ->select([
                SpyProductTableMap::COL_AVALARA_TAX_CODE,
                SpyProductTableMap::COL_SKU,
            ])
            ->find()
            ->toKeyValue(SpyProductTableMap::COL_SKU, SpyProductTableMap::COL_AVALARA_TAX_CODE);
    }

    /**
     * @module Store
     * @module Product
     *
     * @param array<string> $productConcreteSkus
     * @param string $storeName
     *
     * @return array<\Generated\Shared\Transfer\StockProductTransfer>
     */
    public function getStockProductsByProductConcreteSkusForStore(array $productConcreteSkus, string $storeName): array
    {
        $stockProductEntities = $this->getFactory()
            ->getStockProductPropelQuery()
            ->leftJoinWithStock()
            ->orderByIsNeverOutOfStock(Criteria::ASC)
            ->orderByQuantity(Criteria::ASC)
            ->useStockQuery(null, Criteria::LEFT_JOIN)
                ->filterByIsActive(true)
                ->leftJoinStockStore()
                ->useStockStoreQuery(null, Criteria::LEFT_JOIN)
                    ->useStoreQuery(null, Criteria::LEFT_JOIN)
                        ->filterByName($storeName)
                    ->endUse()
                ->endUse()
            ->endUse()
            ->leftJoinWithSpyProduct()
            ->useSpyProductQuery(null, Criteria::LEFT_JOIN)
                ->filterBySku_In($productConcreteSkus)
                ->orderBySku()
            ->endUse()
            ->where('(' . SpyStockProductTableMap::COL_IS_NEVER_OUT_OF_STOCK . '=true OR ' . SpyStockProductTableMap::COL_QUANTITY . '>0)')
            ->find();

        if ($stockProductEntities->count() === 0) {
            return [];
        }

        return $this->getFactory()
            ->createStockProductMapper()
            ->mapStockProductEntitiesToStockProductTransfers($stockProductEntities->getData());
    }
}
