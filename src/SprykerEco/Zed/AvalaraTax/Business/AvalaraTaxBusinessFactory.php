<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerEco\Zed\AvalaraTax\AvalaraTaxDependencyProvider;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilder;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\CartItemAvalaraTaxCalculatorInterface;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\MultiShipmentCartItemAvalaraTaxCalculator;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\SingleShipmentCartItemAvalaraTaxCalculator;
use SprykerEco\Zed\AvalaraTax\Business\Checker\AvalaraTaxQuoteChecker;
use SprykerEco\Zed\AvalaraTax\Business\Checker\AvalaraTaxQuoteCheckerInterface;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraResolveAddressExecutor;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraResolveAddressExecutorInterface;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutor;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface;
use SprykerEco\Zed\AvalaraTax\Business\Expander\AvalaraTaxCodeExpander;
use SprykerEco\Zed\AvalaraTax\Business\Expander\AvalaraTaxCodeExpanderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Expander\WarehouseExpander;
use SprykerEco\Zed\AvalaraTax\Business\Expander\WarehouseExpanderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraResolveAddressRequestMapper;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraResolveAddressRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapper;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapper;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Reader\StockProductReader;
use SprykerEco\Zed\AvalaraTax\Business\Reader\StockProductReaderInterface;
use SprykerEco\Zed\AvalaraTax\Business\StrategyResolver\CartItemTaxCalculatorStrategyResolver;
use SprykerEco\Zed\AvalaraTax\Business\StrategyResolver\CartItemTaxCalculatorStrategyResolverInterface;
use SprykerEco\Zed\AvalaraTax\Business\Validator\CheckoutDataAddressValidator;
use SprykerEco\Zed\AvalaraTax\Business\Validator\CheckoutDataAddressValidatorInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStockFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface;

/**
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxEntityManagerInterface getEntityManager()
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 */
class AvalaraTaxBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Calculator\CartItemAvalaraTaxCalculatorInterface
     */
    public function createSingleShipmentCartItemTaxCalculator(): CartItemAvalaraTaxCalculatorInterface
    {
        return new SingleShipmentCartItemAvalaraTaxCalculator(
            $this->createAvalaraTransactionExecutor(),
            $this->getMoneyFacade(),
            $this->getConfig(),
            $this->getUtilEncodingService(),
            $this->getCreateTransactionRequestAfterPlugins(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Calculator\CartItemAvalaraTaxCalculatorInterface
     */
    public function createMultiShipmentCartItemTaxCalculator(): CartItemAvalaraTaxCalculatorInterface
    {
        return new MultiShipmentCartItemAvalaraTaxCalculator(
            $this->createAvalaraTransactionExecutor(),
            $this->getMoneyFacade(),
            $this->getConfig(),
            $this->getUtilEncodingService(),
            $this->getCreateTransactionRequestAfterPlugins(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Validator\CheckoutDataAddressValidatorInterface
     */
    public function createCheckoutDataAddressValidator(): CheckoutDataAddressValidatorInterface
    {
        return new CheckoutDataAddressValidator($this->createAvalaraResolveAddressExecutor());
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface
     */
    public function createAvalaraTransactionExecutor(): AvalaraTransactionExecutorInterface
    {
        return new AvalaraTransactionExecutor(
            $this->createAvalaraTransactionBuilder(),
            $this->createAvalaraTransactionRequestMapper(),
            $this->createAvalaraTransactionResponseMapper(),
            $this->getEntityManager(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraResolveAddressExecutorInterface
     */
    public function createAvalaraResolveAddressExecutor(): AvalaraResolveAddressExecutorInterface
    {
        return new AvalaraResolveAddressExecutor(
            $this->createAvalaraResolveAddressRequestMapper(),
            $this->getAvalaraTaxClient(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface
     */
    public function createAvalaraTransactionRequestMapper(): AvalaraTransactionRequestMapperInterface
    {
        return new AvalaraTransactionRequestMapper(
            $this->getConfig(),
            $this->getMoneyFacade(),
            $this->getCreateTransactionRequestExpanderPlugins(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface
     */
    public function createAvalaraTransactionResponseMapper(): AvalaraTransactionResponseMapperInterface
    {
        return new AvalaraTransactionResponseMapper($this->getUtilEncodingService());
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraResolveAddressRequestMapperInterface
     */
    public function createAvalaraResolveAddressRequestMapper(): AvalaraResolveAddressRequestMapperInterface
    {
        return new AvalaraResolveAddressRequestMapper();
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface
     */
    public function createAvalaraTransactionBuilder(): AvalaraTransactionBuilderInterface
    {
        return new AvalaraTransactionBuilder($this->getAvalaraTransactionBuilder());
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Expander\AvalaraTaxCodeExpanderInterface
     */
    public function createAvalaraTaxCodeExpander(): AvalaraTaxCodeExpanderInterface
    {
        return new AvalaraTaxCodeExpander($this->getRepository());
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Checker\AvalaraTaxQuoteCheckerInterface
     */
    public function createAvalaraTaxQuoteChecker(): AvalaraTaxQuoteCheckerInterface
    {
        return new AvalaraTaxQuoteChecker();
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Expander\WarehouseExpanderInterface
     */
    public function createWarehouseExpander(): WarehouseExpanderInterface
    {
        return new WarehouseExpander(
            $this->createStockProductReader(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Reader\StockProductReaderInterface
     */
    public function createStockProductReader(): StockProductReaderInterface
    {
        return new StockProductReader(
            $this->getRepository(),
            $this->getStockFacade(),
        );
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface
     */
    public function getMoneyFacade(): AvalaraTaxToMoneyFacadeInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::FACADE_MONEY);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStockFacadeInterface
     */
    public function getStockFacade(): AvalaraTaxToStockFacadeInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::FACADE_STOCK);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): AvalaraTaxToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface
     */
    public function getAvalaraTaxClient(): AvalaraTaxToAvalaraTaxClientInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::AVALARA_TAX_CLIENT);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToTransactionBuilderInterface
     */
    public function getAvalaraTransactionBuilder(): AvalaraTaxToTransactionBuilderInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::AVALARA_TRANSACTION_BUILDER);
    }

    /**
     * @return array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestExpanderPluginInterface>
     */
    public function getCreateTransactionRequestExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::PLUGINS_CREATE_TRANSACTION_REQUEST_EXPANDER);
    }

    /**
     * @return array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestAfterPluginInterface>
     */
    public function getCreateTransactionRequestAfterPlugins(): array
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::PLUGINS_CREATE_TRANSACTION_REQUEST_AFTER);
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. {@Link createMultiShipmentCartItemTaxCalculator()} instead.
     *
     * @return \SprykerEco\Zed\AvalaraTax\Business\StrategyResolver\CartItemTaxCalculatorStrategyResolverInterface
     */
    public function createProductItemTaxRateCalculatorStrategyResolver(): CartItemTaxCalculatorStrategyResolverInterface
    {
        $strategyContainer = [];

        $strategyContainer[CartItemTaxCalculatorStrategyResolver::STRATEGY_KEY_WITHOUT_MULTI_SHIPMENT] = function () {
            return $this->createSingleShipmentCartItemTaxCalculator();
        };

        $strategyContainer[CartItemTaxCalculatorStrategyResolver::STRATEGY_KEY_WITH_MULTI_SHIPMENT] = function () {
            return $this->createMultiShipmentCartItemTaxCalculator();
        };

        return new CartItemTaxCalculatorStrategyResolver($strategyContainer);
    }
}
