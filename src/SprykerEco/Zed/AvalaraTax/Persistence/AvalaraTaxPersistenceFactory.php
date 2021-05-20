<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

use Orm\Zed\Product\Persistence\SpyProductAbstractQuery;
use Orm\Zed\Product\Persistence\SpyProductQuery;
use Orm\Zed\Stock\Persistence\SpyStockProductQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use SprykerEco\Zed\AvalaraTax\AvalaraTaxDependencyProvider;
use SprykerEco\Zed\AvalaraTax\Persistence\Propel\Mapper\StockProductMapper;
use SprykerEco\Zed\AvalaraTax\Persistence\Propel\Mapper\TaxAvalaraLogApiMapper;

/**
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface getEntityManager()
 */
class AvalaraTaxPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \SprykerEco\Zed\AvalaraTax\Persistence\Propel\Mapper\TaxAvalaraLogApiMapper
     */
    public function createTaxAvalaraLogApiMapper(): TaxAvalaraLogApiMapper
    {
        return new TaxAvalaraLogApiMapper();
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Persistence\Propel\Mapper\StockProductMapper
     */
    public function createStockProductMapper(): StockProductMapper
    {
        return new StockProductMapper();
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function getProductAbstractPropelQuery(): SpyProductAbstractQuery
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::PROPEL_QUERY_PRODUCT_ABSTRACT);
    }

    /**
     * @return \Orm\Zed\Product\Persistence\SpyProductQuery
     */
    public function getProductPropelQuery(): SpyProductQuery
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::PROPEL_QUERY_PRODUCT);
    }

    /**
     * @return \Orm\Zed\Stock\Persistence\SpyStockProductQuery
     */
    public function getStockProductPropelQuery(): SpyStockProductQuery
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::PROPEL_QUERY_STOCK_PRODUCT);
    }
}
