<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SpykerEcoTest\Zed\AvalaraTax\Business;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StockProductTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Shared\Kernel\Transfer\Exception\NullValueException;

class ExpandQuoteItemsWithWarehouseTest extends Unit
{
    protected const DEFAULT_STORE_NAME = 'DE';

    /**
     * @var \SprykerEcoTest\Zed\AvalaraTax\AvalaraTaxBusinessTester
     */
    protected $tester;

    /**
     * @var \Generated\Shared\Transfer\StockTransfer
     */
    protected $storeTransfer;

    /**
     * @var \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected $firstProductConcreteTransfer;

    /**
     * @var \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected $secondProductConcreteTransfer;

    /**
     * @var \Generated\Shared\Transfer\QuoteTransfer
     */
    protected $quoteTransfer;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::DEFAULT_STORE_NAME]);

        $this->firstProductConcreteTransfer = $this->tester->haveFullProduct();
        $this->secondProductConcreteTransfer = $this->tester->haveFullProduct();

        $this->quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => $this->firstProductConcreteTransfer->getSkuOrFail()])
            ->withItem([ItemTransfer::SKU => $this->secondProductConcreteTransfer->getSkuOrFail()])
            ->withStore([StoreTransfer::NAME => static::DEFAULT_STORE_NAME])
            ->build();
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseWithEmptyQuote(): void
    {
        // Arrange
        $quoteTransfer = new QuoteTransfer();

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($quoteTransfer);

        // Assert
        $this->assertEmpty($quoteTransfer->getItems());
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseWithoutStore(): void
    {
        // Arrange
        $this->quoteTransfer->setStore(null);

        // Assert
        $this->expectException(NullValueException::class);

        // Act
        $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseInCaseEmptyStockProduct(): void
    {
        // Arrange

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $this->assertNull($quoteTransfer->getItems()->offsetGet(0)->getWarehouse());
        $this->assertNull($quoteTransfer->getItems()->offsetGet(1)->getWarehouse());
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseWithStock(): void
    {
        // Arrange
        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 10,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->secondProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 10,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $this->assertNotNull($quoteTransfer->getItems()->offsetGet(0)->getWarehouse()->getName());
        $this->assertNotNull($quoteTransfer->getItems()->offsetGet(1)->getWarehouse()->getName());
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseWithEmptyStock(): void
    {
        // Arrange
        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 10,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->secondProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 0,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $this->assertNotNull($quoteTransfer->getItems()->offsetGet(0)->getWarehouse()->getName());
        $this->assertNull($quoteTransfer->getItems()->offsetGet(1)->getWarehouse());
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseWithEmptyStoreSpecificStock(): void
    {
        // Arrange
        $this->tester->haveProductInStockForStore($this->tester->haveStore([StoreTransfer::NAME => 'AT']), [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 10,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $this->assertNull($quoteTransfer->getItems()->offsetGet(0)->getWarehouse());
        $this->assertNull($quoteTransfer->getItems()->offsetGet(1)->getWarehouse());
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseCheckQuantityPriority(): void
    {
        // Arrange
        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 10,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 12,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 7,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $stockProductTransfer = $this->tester->findStockProductWithMaximumStockQuantity($this->firstProductConcreteTransfer);

        $this->assertSame(12, $stockProductTransfer->getQuantityOrFail()->toInt());
        $this->assertSame(
            $quoteTransfer->getItems()->offsetGet(0)->getWarehouse()->getName(),
            $stockProductTransfer->getStockTypeOrFail()
        );
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseCheckIsNeverOutStockProperty(): void
    {
        // Arrange
        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 20,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 11,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 3,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => true,
        ]);

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $stockProductTransfer = $this->tester->findStockProductWithMaximumStockQuantity($this->firstProductConcreteTransfer);

        $this->assertSame(3, $stockProductTransfer->getQuantityOrFail()->toInt());
        $this->assertTrue($stockProductTransfer->getIsNeverOutOfStockOrFail());

        $this->assertSame(
            $quoteTransfer->getItems()->offsetGet(0)->getWarehouse()->getName(),
            $stockProductTransfer->getStockTypeOrFail()
        );
    }

    /**
     * @return void
     */
    public function testExpandQuoteItemsWithWarehouseCheckIsActiveProperty(): void
    {
        // Arrange
        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 14,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 13,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $this->tester->haveProductInStockForStore($this->storeTransfer, [
            StockProductTransfer::SKU => $this->firstProductConcreteTransfer->getSku(),
            StockProductTransfer::QUANTITY => 16,
            StockProductTransfer::IS_NEVER_OUT_OF_STOCK => false,
        ]);

        $stockProductTransfer = $this->tester->findStockProductWithMaximumStockQuantity($this->firstProductConcreteTransfer);
        $this->tester->deactivateStock($stockProductTransfer->getStockTypeOrFail());

        // Act
        $quoteTransfer = $this->tester->getFacade()->expandQuoteItemsWithWarehouse($this->quoteTransfer);

        // Assert
        $stockProductTransfer = $this->tester->findStockProductWithMaximumStockQuantity($this->firstProductConcreteTransfer);

        $this->assertSame(14, $stockProductTransfer->getQuantityOrFail()->toInt());
        $this->assertSame(
            $quoteTransfer->getItems()->offsetGet(0)->getWarehouse()->getName(),
            $stockProductTransfer->getStockTypeOrFail()
        );
    }
}
