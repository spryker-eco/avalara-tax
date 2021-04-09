<?php

namespace SpykerEcoTest\Zed\AvalaraTax\Business;

use Avalara\TransactionBuilder;
use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\CalculableObjectBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManager;
use stdClass;

class AvalaraTaxFacadeTest extends Unit
{
    protected const TEST_AVALARA_TAX_CODE = 'TESTCODE';

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_GROSS
     */
    protected const PRICE_MODE_GROSS = 'GROSS_MODE';

    protected const TEST_SKU_1 = 'test-sku-1';
    protected const TEST_SUM_PRICE_1 = '33265';

    protected const TEST_SKU_2 = 'test-sku-2';
    protected const TEST_SUM_PRICE_2 = '36742';

    protected const TEST_CITY_NAME_1 = 'Detroit';
    protected const TEST_ZIP_CODE_1 = '48201';

    protected const TEST_CITY_NAME_2 = 'Auburn Hills';
    protected const TEST_ZIP_CODE_2 = '48326';

    protected const TEST_COUNTRY = 'US';

    /**
     * @var \SprykerEcoTest\Zed\AvalaraTax\AvalaraTaxBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testExpandProductConcreteTransferWithAvalaraTaxCodeWillProvideAvalaraTaxCode(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveFullProduct([], [ProductAbstractTransfer::AVALARA_TAX_CODE => static::TEST_AVALARA_TAX_CODE]);

        // Act
        $productConcreteTransfer = $this->tester->getFacade()->expandProductConcreteTransferWithAvalaraTaxCode($productConcreteTransfer);

        // Assert
        $this->assertEquals(static::TEST_AVALARA_TAX_CODE, $productConcreteTransfer->getAvalaraTaxCode());
    }

    /**
     * @return void
     */
    public function testExpandItemTransferWithAvalaraTaxCodeWillProvideAvalaraTaxCode(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveFullProduct([ProductConcreteTransfer::AVALARA_TAX_CODE => static::TEST_AVALARA_TAX_CODE]);

        $itemTransfer = (new ItemBuilder([ItemTransfer::SKU => $productConcreteTransfer->getSku()]))->build();
        $cartChangeTransfer = (new CartChangeTransfer())->addItem($itemTransfer);

        // Act
        $cartChangeTransfer = $this->tester->getFacade()->expandItemTransfersWithAvalaraTaxCode($cartChangeTransfer);

        // Assert
        $this->assertEquals(static::TEST_AVALARA_TAX_CODE, $cartChangeTransfer->getItems()->offsetGet(0)->getAvalaraTaxCode());
    }

    /**
     * @return void
     */
    public function testCalculateTaxWillCalculateTaxWithMultiAddressShipmentOrder(): void
    {
        // Arrange
        $transactionModelMock = $this->getTransactionModelMock('avalara_api_multi_address_response.json');
        $this->tester->mockFactoryMethod('createTransactionBuilder', $this->createTransactionBuilderMock($transactionModelMock));
        $this->tester->mockFactoryMethod('getEntityManager', new AvalaraTaxEntityManager());

        $itemTransfer1 = $this->createItemTransfer1();
        $itemTransfer2 = $this->createItemTransfer2();

        $quoteTransfer = (new QuoteBuilder())->build();
        $calculableObjectTransfer = (new CalculableObjectBuilder([CalculableObjectTransfer::PRICE_MODE => static::PRICE_MODE_GROSS]))
            ->withCurrency()
            ->build();
        $calculableObjectTransfer->setOriginalQuote($quoteTransfer)
            ->addItem($itemTransfer1)
            ->addItem($itemTransfer2);

        // Act
        $this->tester->getFacade()->calculateTax($calculableObjectTransfer);

        // Assert
        $this->assertNotNull($calculableObjectTransfer->getOriginalQuote()->getAvalaraCreateTransactionResponse());

        /** @var \Generated\Shared\Transfer\ItemTransfer $resultItemTransfer1 */
        $resultItemTransfer1 = $calculableObjectTransfer->getItems()->offsetGet(0);
        $this->assertNotNull($resultItemTransfer1->getTaxRate());
        $this->assertNotNull($resultItemTransfer1->getSumTaxAmount());

        /** @var \Generated\Shared\Transfer\ItemTransfer $resultItemTransfer1 */
        $resultItemTransfer2 = $calculableObjectTransfer->getItems()->offsetGet(1);
        $this->assertNotNull($resultItemTransfer2->getTaxRate());
        $this->assertNotNull($resultItemTransfer2->getSumTaxAmount());
    }

    /**
     * @return void
     */
    public function testCalculateTaxWillCalculateTaxWithSingleAddressShipmentOrder(): void
    {
        // Arrange
        $transactionModelMock = $this->getTransactionModelMock('avalara_api_multi_address_response.json');
        $this->tester->mockFactoryMethod('createTransactionBuilder', $this->createTransactionBuilderMock($transactionModelMock));
        $this->tester->mockFactoryMethod('getEntityManager', new AvalaraTaxEntityManager());

        $itemTransfer1 = $this->createItemTransfer1(false);
        $itemTransfer2 = $this->createItemTransfer2(false);

        $quoteTransfer = (new QuoteBuilder())->build();
        $calculableObjectTransfer = (new CalculableObjectBuilder([CalculableObjectTransfer::PRICE_MODE => static::PRICE_MODE_GROSS]))
            ->withShippingAddress([
                AddressTransfer::COUNTRY => static::TEST_COUNTRY,
                AddressTransfer::CITY => static::TEST_CITY_NAME_1,
                AddressTransfer::ZIP_CODE => static::TEST_ZIP_CODE_1,
            ])
            ->withCurrency()
            ->build();
        $calculableObjectTransfer->setOriginalQuote($quoteTransfer)
            ->addItem($itemTransfer1)
            ->addItem($itemTransfer2);

        // Act
        $this->tester->getFacade()->calculateTax($calculableObjectTransfer);

        // Assert
        $this->assertNotNull($calculableObjectTransfer->getOriginalQuote()->getAvalaraCreateTransactionResponse());

        /** @var \Generated\Shared\Transfer\ItemTransfer $resultItemTransfer1 */
        $resultItemTransfer1 = $calculableObjectTransfer->getItems()->offsetGet(0);
        $this->assertNotNull($resultItemTransfer1->getTaxRate());
        $this->assertNotNull($resultItemTransfer1->getSumTaxAmount());

        /** @var \Generated\Shared\Transfer\ItemTransfer $resultItemTransfer1 */
        $resultItemTransfer2 = $calculableObjectTransfer->getItems()->offsetGet(1);
        $this->assertNotNull($resultItemTransfer2->getTaxRate());
        $this->assertNotNull($resultItemTransfer2->getSumTaxAmount());
    }

    /**
     * @param \stdClass $transactionModelMock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Avalara\TransactionBuilder
     */
    protected function createTransactionBuilderMock(stdClass $transactionModelMock): TransactionBuilder
    {
        $transactionBuilderMock = $this->getMockBuilder(TransactionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $transactionBuilderMock->method('withType')->willReturnSelf();
        $transactionBuilderMock->method('withCurrencyCode')->willReturnSelf();
        $transactionBuilderMock->method('withAddress')->willReturnSelf();
        $transactionBuilderMock->method('withLine')->willReturnSelf();
        $transactionBuilderMock->method('withLineCustomFields')->willReturnSelf();
        $transactionBuilderMock->method('withLineDescription')->willReturnSelf();
        $transactionBuilderMock->method('withLineTaxIncluded')->willReturnSelf();
        $transactionBuilderMock->method('withLineAddress')->willReturnSelf();
        $transactionBuilderMock->method('create')->willReturn($transactionModelMock);

        return $transactionBuilderMock;
    }

    /**
     * @param string $fileName
     *
     * @return \stdClass
     */
    protected function getTransactionModelMock(string $fileName): stdClass
    {
        $avalaraApiResponseJson = file_get_contents(codecept_data_dir($fileName));

        return json_decode($avalaraApiResponseJson);
    }

    /**
     * @param bool $withAddress
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function createItemTransfer1(bool $withAddress = true): ItemTransfer
    {
        $itemBuilder = (new ItemBuilder([
            ItemTransfer::AVALARA_TAX_CODE => static::TEST_AVALARA_TAX_CODE,
            ItemTransfer::SKU => static::TEST_SKU_1,
            ItemTransfer::GROUP_KEY => static::TEST_SKU_1,
            ItemTransfer::AMOUNT => 1,
            ItemTransfer::SUM_PRICE => static::TEST_SUM_PRICE_1,
            ItemTransfer::SUM_DISCOUNT_AMOUNT_AGGREGATION => 0,
        ]));

        if (!$withAddress) {
            return $itemBuilder->build();
        }

        return $itemBuilder->withShipment([
            ShipmentTransfer::SHIPPING_ADDRESS => [
                AddressTransfer::COUNTRY => static::TEST_COUNTRY,
                AddressTransfer::CITY => static::TEST_CITY_NAME_1,
                AddressTransfer::ZIP_CODE => static::TEST_ZIP_CODE_1,
            ],
        ])->build();
    }

    /**
     * @param bool $withAddress
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function createItemTransfer2(bool $withAddress = true): ItemTransfer
    {
        $itemBuilder = (new ItemBuilder([
            ItemTransfer::AVALARA_TAX_CODE => static::TEST_AVALARA_TAX_CODE,
            ItemTransfer::SKU => static::TEST_SKU_2,
            ItemTransfer::GROUP_KEY => static::TEST_SKU_2,
            ItemTransfer::AMOUNT => 1,
            ItemTransfer::SUM_PRICE => static::TEST_SUM_PRICE_2,
            ItemTransfer::SUM_DISCOUNT_AMOUNT_AGGREGATION => 0,
        ]));

        if (!$withAddress) {
            return $itemBuilder->build();
        }

        return $itemBuilder->withShipment([
            ShipmentTransfer::SHIPPING_ADDRESS => [
                AddressTransfer::COUNTRY => static::TEST_COUNTRY,
                AddressTransfer::CITY => static::TEST_CITY_NAME_2,
                AddressTransfer::ZIP_CODE => static::TEST_ZIP_CODE_2,
            ],
        ])->build();
    }
}
