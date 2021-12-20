<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AvalaraAddressTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionTransfer;
use Generated\Shared\Transfer\AvalaraLineItemTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\StockAddressTransfer;
use SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface;

class AvalaraTransactionRequestMapper implements AvalaraTransactionRequestMapperInterface
{
    /**
     * @var string
     */
    public const CART_ITEM_AVALARA_LINE_TYPE = 'cart-item';

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_GROSS
     *
     * @var string
     */
    protected const PRICE_MODE_GROSS = 'GROSS_MODE';

    /**
     * @uses \Avalara\TransactionAddressType::C_SHIPTO
     *
     * @var string
     */
    protected const AVALARA_SHIP_TO_ADDRESS_TYPE = 'ShipTo';

    /**
     * @uses \Avalara\TransactionAddressType::C_SHIPFROM
     *
     * @var string
     */
    protected const AVALARA_SHIP_FROM_ADDRESS_TYPE = 'ShipFrom';

    /**
     * @var \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig
     */
    protected $avalaraTaxConfig;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @var array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestExpanderPluginInterface>
     */
    protected $createTransactionRequestExpanderPluginInterfaces;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig $avalaraTaxConfig
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface $moneyFacade
     * @param array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestExpanderPluginInterface> $createTransactionRequestExpanderPluginInterfaces
     */
    public function __construct(
        AvalaraTaxConfig $avalaraTaxConfig,
        AvalaraTaxToMoneyFacadeInterface $moneyFacade,
        array $createTransactionRequestExpanderPluginInterfaces
    ) {
        $this->avalaraTaxConfig = $avalaraTaxConfig;
        $this->moneyFacade = $moneyFacade;
        $this->createTransactionRequestExpanderPluginInterfaces = $createTransactionRequestExpanderPluginInterfaces;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer
     */
    public function mapCalculableObjectTransferToAvalaraCreateTransactionRequestTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
    ): AvalaraCreateTransactionRequestTransfer {
        $avalaraCreateTransactionTransfer = $this->createAvalaraCreateTransactionTransfer($calculableObjectTransfer);

        $isTaxIncluded = $this->isTaxIncluded($calculableObjectTransfer);
        foreach ($calculableObjectTransfer->getItems() as $itemTransfer) {
            $avalaraLineItemTransfer = $this->mapItemTransferToAvalaraLineTransfer($itemTransfer, new AvalaraLineItemTransfer());
            $avalaraLineItemTransfer->setTaxIncluded($isTaxIncluded);
            $avalaraCreateTransactionTransfer->addLine($avalaraLineItemTransfer);
        }

        return $this->executeCreateTransactionRequestExpanderPlugins(
            $calculableObjectTransfer,
            $avalaraCreateTransactionRequestTransfer->setTransaction($avalaraCreateTransactionTransfer),
        );
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionTransfer
     */
    protected function createAvalaraCreateTransactionTransfer(CalculableObjectTransfer $calculableObjectTransfer): AvalaraCreateTransactionTransfer
    {
        $orderReference = $this->extractOrderReferenceFromCalculableObjectTransfer($calculableObjectTransfer);

        $avalaraCreateTransactionTransfer = (new AvalaraCreateTransactionTransfer())
            ->setCurrencyCode($calculableObjectTransfer->getCurrencyOrFail()->getCodeOrFail())
            ->setCompanyCode($this->avalaraTaxConfig->getCompanyCode())
            ->setWithCommit($this->isTransactionCommitable($orderReference))
            ->setPurchaseOrderNo($orderReference);

        if (!$this->isSingleAddressShipment($calculableObjectTransfer)) {
            return $avalaraCreateTransactionTransfer;
        }

        $avalaraShippingAddressTransfer = (new AvalaraAddressTransfer())
            ->setAddress($calculableObjectTransfer->getShippingAddress())
            ->setType(static::AVALARA_SHIP_TO_ADDRESS_TYPE);

        return $avalaraCreateTransactionTransfer->setShippingAddress($avalaraShippingAddressTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\AvalaraLineItemTransfer $avalaraLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraLineItemTransfer
     */
    protected function mapItemTransferToAvalaraLineTransfer(
        ItemTransfer $itemTransfer,
        AvalaraLineItemTransfer $avalaraLineItemTransfer
    ): AvalaraLineItemTransfer {
        $avalaraLineItemTransfer->fromArray($itemTransfer->toArray(), true);
        $avalaraLineItemTransfer
            ->setReference1(static::CART_ITEM_AVALARA_LINE_TYPE)
            ->setReference2($itemTransfer->getGroupKeyOrFail())
            ->setAmount($this->calculateItemAmount($itemTransfer))
            ->setItemCode($itemTransfer->getSkuOrFail())
            ->setTaxCode($itemTransfer->getAvalaraTaxCode() ?? '')
            ->setDescription($itemTransfer->getNameOrFail());

        if (!$itemTransfer->getShipment() && !$itemTransfer->getWarehouse()) {
            return $avalaraLineItemTransfer;
        }

        $avalaraLineItemTransfer = $this->mapItemTransferShippingAddressToAvalaraLineItemTransfer($itemTransfer, $avalaraLineItemTransfer);
        $avalaraLineItemTransfer = $this->mapItemTransferStockAddressToAvalaraItemTransfer($itemTransfer, $avalaraLineItemTransfer);

        return $avalaraLineItemTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\AvalaraLineItemTransfer $avalaraLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraLineItemTransfer
     */
    protected function mapItemTransferShippingAddressToAvalaraLineItemTransfer(
        ItemTransfer $itemTransfer,
        AvalaraLineItemTransfer $avalaraLineItemTransfer
    ): AvalaraLineItemTransfer {
        if (!$itemTransfer->getShipment()) {
            return $avalaraLineItemTransfer;
        }

        $avalaraShippingAddressTransfer = (new AvalaraAddressTransfer())->setType(static::AVALARA_SHIP_TO_ADDRESS_TYPE);
        $avalaraShippingAddressTransfer = $this->mapShipmentTransferToAvalaraAddressTransfer(
            $itemTransfer->getShipmentOrFail(),
            $avalaraShippingAddressTransfer,
        );

        return $avalaraLineItemTransfer->setShippingAddress($avalaraShippingAddressTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\AvalaraLineItemTransfer $avalaraLineItemTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraLineItemTransfer
     */
    protected function mapItemTransferStockAddressToAvalaraItemTransfer(
        ItemTransfer $itemTransfer,
        AvalaraLineItemTransfer $avalaraLineItemTransfer
    ): AvalaraLineItemTransfer {
        if (!$itemTransfer->getWarehouse()) {
            return $avalaraLineItemTransfer;
        }

        $stockAddressTransfer = $itemTransfer->getWarehouseOrFail()->getAddress();
        if ($stockAddressTransfer === null) {
            return $avalaraLineItemTransfer;
        }

        $avalaraShippingAddressTransfer = (new AvalaraAddressTransfer())->setType(static::AVALARA_SHIP_FROM_ADDRESS_TYPE);
        $avalaraShippingAddressTransfer = $this->mapStockAddressTransferToAvalaraAddressTransfer(
            $stockAddressTransfer,
            $avalaraShippingAddressTransfer,
        );

        return $avalaraLineItemTransfer->setSourceAddress($avalaraShippingAddressTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     * @param \Generated\Shared\Transfer\AvalaraAddressTransfer $avalaraAddressTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraAddressTransfer
     */
    protected function mapShipmentTransferToAvalaraAddressTransfer(
        ShipmentTransfer $shipmentTransfer,
        AvalaraAddressTransfer $avalaraAddressTransfer
    ): AvalaraAddressTransfer {
        return $avalaraAddressTransfer->setAddress($shipmentTransfer->getShippingAddressOrFail());
    }

    /**
     * @param \Generated\Shared\Transfer\StockAddressTransfer $stockAddressTransfer
     * @param \Generated\Shared\Transfer\AvalaraAddressTransfer $avalaraAddressTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraAddressTransfer
     */
    protected function mapStockAddressTransferToAvalaraAddressTransfer(
        StockAddressTransfer $stockAddressTransfer,
        AvalaraAddressTransfer $avalaraAddressTransfer
    ): AvalaraAddressTransfer {
        $addressTransfer = (new AddressTransfer())->fromArray($stockAddressTransfer->toArray(), true);
        $addressTransfer->setIso2Code($stockAddressTransfer->getCountryOrFail()->getIso2CodeOrFail());

        return $avalaraAddressTransfer->setAddress($addressTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer
     */
    protected function executeCreateTransactionRequestExpanderPlugins(
        CalculableObjectTransfer $calculableObjectTransfer,
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
    ): AvalaraCreateTransactionRequestTransfer {
        foreach ($this->createTransactionRequestExpanderPluginInterfaces as $createTransactionRequestExpanderPluginInterface) {
            $avalaraCreateTransactionRequestTransfer = $createTransactionRequestExpanderPluginInterface->expand(
                $avalaraCreateTransactionRequestTransfer,
                $calculableObjectTransfer,
            );
        }

        return $avalaraCreateTransactionRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function isSingleAddressShipment(CalculableObjectTransfer $calculableObjectTransfer): bool
    {
        return $calculableObjectTransfer->getShippingAddress() && $calculableObjectTransfer->getShippingAddressOrFail()->getZipCode();
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return float
     */
    protected function calculateItemAmount(ItemTransfer $itemTransfer): float
    {
        return $this->moneyFacade->convertIntegerToDecimal($itemTransfer->getSumPriceOrFail() - $itemTransfer->getSumDiscountAmountAggregation() ?? 0);
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return bool
     */
    protected function isTaxIncluded(CalculableObjectTransfer $calculableObjectTransfer): bool
    {
        return $calculableObjectTransfer->getPriceModeOrFail() === static::PRICE_MODE_GROSS;
    }

    /**
     * @param string|null $orderReference
     *
     * @return bool
     */
    protected function isTransactionCommitable(?string $orderReference): bool
    {
        return $orderReference !== null && $this->avalaraTaxConfig->getIsTransactionCommitAfterOrderPlacementEnabled();
    }

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return string|null
     */
    protected function extractOrderReferenceFromCalculableObjectTransfer(CalculableObjectTransfer $calculableObjectTransfer): ?string
    {
        if (!$calculableObjectTransfer->getOriginalQuote()) {
            return null;
        }

        return $calculableObjectTransfer->getOriginalQuoteOrFail()->getOrderReference();
    }
}
