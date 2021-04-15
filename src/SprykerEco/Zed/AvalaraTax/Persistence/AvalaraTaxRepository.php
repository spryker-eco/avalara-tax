<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

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
     * @param string[] $productConcreteSkus
     *
     * @return string[]
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
}
