<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEcoTest\Zed\AvalaraTax;

use Codeception\Actor;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\SpyStockEntityTransfer;
use Generated\Shared\Transfer\StockProductTransfer;
use Orm\Zed\Stock\Persistence\Map\SpyStockProductTableMap;
use Orm\Zed\Stock\Persistence\SpyStockProductQuery;
use Orm\Zed\Stock\Persistence\SpyStockQuery;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @method \SprykerEco\Zed\AvalaraTax\Business\AvalaraTaxFacadeInterface getFacade()
 *
 * @SuppressWarnings(PHPMD)
 */
class AvalaraTaxBusinessTester extends Actor
{
    use _generated\AvalaraTaxBusinessTesterActions;

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\StockProductTransfer|null
     */
    public function findStockProductWithMaximumStockQuantity(ProductConcreteTransfer $productConcreteTransfer): ?StockProductTransfer
    {
        $stockProductEntity = SpyStockProductQuery::create()
            ->leftJoinWithStock()
            ->useStockQuery()
                ->filterByIsActive(true)
            ->endUse()
            ->filterByFkProduct($productConcreteTransfer->getIdProductConcreteOrFail())
            ->orderByIsNeverOutOfStock(Criteria::DESC)
            ->orderByQuantity(Criteria::DESC)
            ->where('(' . SpyStockProductTableMap::COL_IS_NEVER_OUT_OF_STOCK . '=1 OR ' . SpyStockProductTableMap::COL_QUANTITY . '>0)')
            ->findOne();

        if (!$stockProductEntity) {
            return null;
        }

        $stockProductTransfer = (new StockProductTransfer())
            ->fromArray($stockProductEntity->toArray(), true);

        return $stockProductTransfer->setStockType($stockProductEntity->getStock()->getName());
    }

    /**
     * @param string $stockName
     *
     * @return void
     */
    public function deactivateStock(string $stockName): void
    {
        SpyStockQuery::create()
            ->filterByName($stockName)
            ->update([ucfirst(SpyStockEntityTransfer::IS_ACTIVE) => false]);
    }
}
