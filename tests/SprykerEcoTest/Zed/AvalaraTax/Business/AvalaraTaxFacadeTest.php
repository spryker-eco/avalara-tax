<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SpykerEcoTest\Zed\AvalaraTax\Business;

use Avalara\AvaTaxMessage;
use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\DataBuilder\AvalaraCreateTransactionResponseBuilder;
use Generated\Shared\DataBuilder\CalculableObjectBuilder;
use Generated\Shared\DataBuilder\CheckoutDataBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\RestAddressBuilder;
use Generated\Shared\DataBuilder\RestShipmentsBuilder;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\RestAddressTransfer;
use Generated\Shared\Transfer\RestShipmentsTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use RuntimeException;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface;
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
    protected const TEST_STORE_NAME = 'US';

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
        $productConcreteTransfer = $this->tester->getFacade()->expandProductConcreteWithAvalaraTaxCode($productConcreteTransfer);

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
        $cartChangeTransfer = $this->tester->getFacade()->expandCartItemsWithAvalaraTaxCode($cartChangeTransfer);

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
        $this->tester->mockFactoryMethod('getAvalaraTransactionBuilder', $this->createTransactionBuilderMock($transactionModelMock));
        $this->tester->mockFactoryMethod('getEntityManager', new AvalaraTaxEntityManager());

        $itemTransfer1 = $this->createItemTransfer1();
        $itemTransfer2 = $this->createItemTransfer2();

        $quoteTransfer = (new QuoteBuilder())->build();
        $calculableObjectTransfer = (new CalculableObjectBuilder([CalculableObjectTransfer::PRICE_MODE => static::PRICE_MODE_GROSS]))
            ->withCurrency()
            ->withStore([StoreTransfer::NAME => static::TEST_STORE_NAME])
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
        $this->tester->mockFactoryMethod('getAvalaraTransactionBuilder', $this->createTransactionBuilderMock($transactionModelMock));
        $this->tester->mockFactoryMethod('getEntityManager', new AvalaraTaxEntityManager());

        $itemTransfer1 = $this->createItemTransfer1(false);
        $itemTransfer2 = $this->createItemTransfer2(false);

        $quoteTransfer = (new QuoteBuilder())->build();
        $calculableObjectTransfer = (new CalculableObjectBuilder([CalculableObjectTransfer::PRICE_MODE => static::PRICE_MODE_GROSS]))
            ->withStore([StoreTransfer::NAME => static::TEST_STORE_NAME])
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
     * @return void
     */
    public function testValidateCheckoutDataShippingAddressWillReturnSuccessfulResponseWhenShippingAddressNotProvided(): void
    {
        // Arrange
        $checkoutDataTransfer = (new CheckoutDataBuilder())->build();
        $checkoutDataTransfer->setShippingAddress(null);

        // Act
        $checkoutResponseTransfer = $this->tester->getFacade()->validateCheckoutDataShippingAddress($checkoutDataTransfer);

        // Assert
        $this->assertTrue($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testValidateCheckoutDataShippingAddressWithSingleAddressWillReturnSuccessfulResponseWhenValidationWasSuccessful(): void
    {
        // Arrange
        $shippingAddress = (new AddressBuilder())->withCountry()->build();
        $checkoutDataTransfer = (new CheckoutDataBuilder())
            ->withShippingAddress($shippingAddress->toArray())
            ->build();

        $addressResolutionModel = (new stdClass());
        $addressResolutionModel->messages = [];
        $avalaraTaxClientMock = $this->createAvalaraTaxClientMock($addressResolutionModel);

        $this->tester->mockFactoryMethod('getAvalaraTaxClient', $avalaraTaxClientMock);

        // Act
        $checkoutResponseTransfer = $this->tester->getFacade()->validateCheckoutDataShippingAddress($checkoutDataTransfer);

        // Assert
        $this->assertTrue($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testValidateCheckoutDataShippingAddressWithMultipleAddressesWillReturnSuccessfulResponseWhenValidationWasSuccessful(): void
    {
        // Arrange
        $checkoutDataTransfer = (new CheckoutDataBuilder([
            CheckoutDataTransfer::SHIPMENTS => [
                $this->createRestShipmentTransfer(),
                $this->createRestShipmentTransfer(),
                $this->createRestShipmentTransfer(),
            ],
        ]))->build();

        $addressResolutionModel = (new stdClass());
        $addressResolutionModel->messages = [];
        $avalaraTaxClientMock = $this->createAvalaraTaxClientMock($addressResolutionModel);

        $this->tester->mockFactoryMethod('getAvalaraTaxClient', $avalaraTaxClientMock);

        // Act
        $checkoutResponseTransfer = $this->tester->getFacade()->validateCheckoutDataShippingAddress($checkoutDataTransfer);

        // Assert
        $this->assertTrue($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testValidateCheckoutDataShippingAddressWillReturnErrorWhenValidationRequestThrowsException(): void
    {
        // Arrange
        $shippingAddress = (new AddressBuilder())->withCountry()->build();
        $checkoutDataTransfer = (new CheckoutDataBuilder())
            ->withShippingAddress($shippingAddress->toArray())
            ->build();

        $errorMessage = 'Invalid address';
        $avalaraTaxClientMock = $this->createAvalaraTaxClientMock(new stdClass());
        $avalaraTaxClientMock->method('resolveAddress')->willThrowException(new RuntimeException($errorMessage));

        $this->tester->mockFactoryMethod('getAvalaraTaxClient', $avalaraTaxClientMock);

        // Act
        $checkoutResponseTransfer = $this->tester->getFacade()->validateCheckoutDataShippingAddress($checkoutDataTransfer);

        // Assert
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
        $this->assertSame($errorMessage, $checkoutResponseTransfer->getErrors()->offsetGet(0)->getMessage());
    }

    /**
     * @return void
     */
    public function testValidateCheckoutDataShippingAddressWillReturnErrorWhenValidationWasNotSuccessful(): void
    {
        // Arrange
        $shippingAddress = (new AddressBuilder())->withCountry()->build();
        $checkoutDataTransfer = (new CheckoutDataBuilder())
            ->withShippingAddress($shippingAddress->toArray())
            ->build();

        $errorMessage = 'Invalid address';

        $avaTaxMessage = new AvaTaxMessage();
        $avaTaxMessage->summary = $errorMessage;

        $addressResolutionModel = (new stdClass());
        $addressResolutionModel->messages = [$avaTaxMessage];

        $avalaraTaxClientMock = $this->createAvalaraTaxClientMock($addressResolutionModel);

        $this->tester->mockFactoryMethod('getAvalaraTaxClient', $avalaraTaxClientMock);

        // Act
        $checkoutResponseTransfer = $this->tester->getFacade()->validateCheckoutDataShippingAddress($checkoutDataTransfer);

        // Assert
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
        $this->assertSame($errorMessage, $checkoutResponseTransfer->getErrors()->offsetGet(0)->getMessage());
    }

    /**
     * @return void
     */
    public function testIsQuoteTaxCalculationValidWillReturnTrueIfRequestWasSuccessful(): void
    {
        // Arrange
        $avalaraCreateTransactionResponseTransfer = (new AvalaraCreateTransactionResponseBuilder([
            AvalaraCreateTransactionResponseTransfer::IS_SUCCESSFUL => true,
        ]))->build();

        $quoteTransfer = (new QuoteBuilder())
            ->build()
            ->setAvalaraCreateTransactionResponse($avalaraCreateTransactionResponseTransfer);
        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = $this->tester->getFacade()->isQuoteTaxCalculationValid($quoteTransfer, $checkoutResponseTransfer);

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testIsQuoteTaxCalculationValidWillReturnFalseAndErrorIfRequestWasNotSuccessful(): void
    {
        // Arrange
        $expectedErrorMessage = 'Some error message';
        $avalaraCreateTransactionResponseTransfer = (new AvalaraCreateTransactionResponseBuilder([
            AvalaraCreateTransactionResponseTransfer::IS_SUCCESSFUL => false,
        ]))->withMessage([MessageTransfer::MESSAGE => $expectedErrorMessage])->build();

        $quoteTransfer = (new QuoteBuilder())
            ->build()
            ->setAvalaraCreateTransactionResponse($avalaraCreateTransactionResponseTransfer);
        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        // Act
        $result = $this->tester->getFacade()->isQuoteTaxCalculationValid($quoteTransfer, $checkoutResponseTransfer);

        // Assert
        $this->assertFalse($result);
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
        $this->assertSame($expectedErrorMessage, $checkoutResponseTransfer->getErrors()->offsetGet(0)->getMessage());
    }

    /**
     * @param \stdClass $transactionModelMock
     *
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    protected function createTransactionBuilderMock(stdClass $transactionModelMock): AvalaraTaxToTransactionBuilderInterface
    {
        $transactionBuilderMock = $this->getMockBuilder(AvalaraTaxToTransactionBuilderInterface::class)
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
        $transactionBuilderMock->method('withCommit')->willReturnSelf();
        $transactionBuilderMock->method('withPurchaseOrderNo')->willReturnSelf();
        $transactionBuilderMock->method('create')->willReturn($transactionModelMock);

        return $transactionBuilderMock;
    }

    /**
     * @param \stdClass|\Avalara\AddressResolutionModel $addressResolutionModel
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface
     */
    protected function createAvalaraTaxClientMock(stdClass $addressResolutionModel): AvalaraTaxToAvalaraTaxClientInterface
    {
        $avalaraTaxClientMock = $this->getMockBuilder(AvalaraTaxToAvalaraTaxClientInterface::class)->getMock();
        $avalaraTaxClientMock->method('resolveAddress')->willReturn($addressResolutionModel);

        return $avalaraTaxClientMock;
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

        $addressTransfer = (new AddressBuilder([
            AddressTransfer::COUNTRY => static::TEST_COUNTRY,
            AddressTransfer::CITY => static::TEST_CITY_NAME_1,
            AddressTransfer::ZIP_CODE => static::TEST_ZIP_CODE_1,
        ]))->build();

        return $itemBuilder->withShipment([
            ShipmentTransfer::SHIPPING_ADDRESS => $addressTransfer->toArray(),
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

        $addressTransfer = (new AddressBuilder([
            AddressTransfer::COUNTRY => static::TEST_COUNTRY,
            AddressTransfer::CITY => static::TEST_CITY_NAME_2,
            AddressTransfer::ZIP_CODE => static::TEST_ZIP_CODE_2,
        ]))->build();

        return $itemBuilder->withShipment([
            ShipmentTransfer::SHIPPING_ADDRESS => $addressTransfer->toArray(),
        ])->build();
    }

    /**
     * @return \Generated\Shared\Transfer\RestShipmentsTransfer
     */
    protected function createRestShipmentTransfer(): RestShipmentsTransfer
    {
        return (new RestShipmentsBuilder([
            RestShipmentsTransfer::SHIPPING_ADDRESS => (new RestAddressBuilder([RestAddressTransfer::COUNTRY => static::TEST_COUNTRY]))->build(),
        ]))->build();
    }
}
