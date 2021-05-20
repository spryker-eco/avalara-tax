<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Reader;

use Generated\Shared\Transfer\StockCriteriaFilterTransfer;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStockFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface;

class StockProductReader implements StockProductReaderInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface
     */
    protected $avalaraTaxRepository;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStockFacadeInterface
     */
    protected $stockFacade;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface $avalaraTaxRepository
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStockFacadeInterface $stockFacade
     */
    public function __construct(
        AvalaraTaxRepositoryInterface $avalaraTaxRepository,
        AvalaraTaxToStockFacadeInterface $stockFacade
    ) {
        $this->avalaraTaxRepository = $avalaraTaxRepository;
        $this->stockFacade = $stockFacade;
    }

    /**
     * @param string[] $skus
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StockProductTransfer[]
     */
    public function getStockProductsIndexedByProductConcreteSku(array $skus, string $storeName): array
    {
        $stockProductsGroupedBySku = [];
        $stockProductTransfers = $this->avalaraTaxRepository->getStockProductsByProductConcreteSkusForStore($skus, $storeName);

        foreach ($stockProductTransfers as $stockProductTransfer) {
            $stockProductsGroupedBySku[$stockProductTransfer->getSkuOrFail()] = $stockProductTransfer;
        }

        return $stockProductsGroupedBySku;
    }

    /**
     * @param string[] $stockNames
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StockTransfer[]
     */
    public function getStocksIndexedByName(array $stockNames, string $storeName): array
    {
        $indexedStockTransfers = [];
        $stockCriteriaFilterTransfer = (new StockCriteriaFilterTransfer())
            ->setIsActive(true)
            ->setStockNames($stockNames)
            ->addStoreName($storeName);

        $stockTransfers = $this->stockFacade->getStocksByStockCriteriaFilter($stockCriteriaFilterTransfer)->getStocks();

        foreach ($stockTransfers as $stockTransfer) {
            $indexedStockTransfers[$stockTransfer->getName()] = $stockTransfer;
        }

        return $indexedStockTransfers;
    }
}
