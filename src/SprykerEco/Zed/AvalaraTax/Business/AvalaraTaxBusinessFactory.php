<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business;

use Avalara\AvaTaxClient;
use Avalara\DocumentType;
use Avalara\TransactionBuilder;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use SprykerEco\Zed\AvalaraTax\AvalaraTaxDependencyProvider;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilder;
use SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\CartItemAvalaraTaxCalculatorInterface;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\MultiShipmentCartItemAvalaraTaxCalculator;
use SprykerEco\Zed\AvalaraTax\Business\Calculator\SingleShipmentCartItemAvalaraTaxCalculator;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutor;
use SprykerEco\Zed\AvalaraTax\Business\Executor\AvalaraTransactionExecutorInterface;
use SprykerEco\Zed\AvalaraTax\Business\Expander\AvalaraTaxSetExpander;
use SprykerEco\Zed\AvalaraTax\Business\Expander\AvalaraTaxSetExpanderInterface;
use SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLogger;
use SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapper;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapper;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraTransactionResponseMapperInterface;
use SprykerEco\Zed\AvalaraTax\Business\StrategyResolver\CartItemTaxCalculatorStrategyResolver;
use SprykerEco\Zed\AvalaraTax\Business\StrategyResolver\CartItemTaxCalculatorStrategyResolverInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface;
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
            $this->getUtilEncodingService()
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
            $this->getUtilEncodingService()
        );
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
            $this->createAvalaraTransactionLogger()
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
            $this->getCreateTransactionRequestExpanderPlugins()
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
     * @return \SprykerEco\Zed\AvalaraTax\Business\Builder\AvalaraTransactionBuilderInterface
     */
    public function createAvalaraTransactionBuilder(): AvalaraTransactionBuilderInterface
    {
        return new AvalaraTransactionBuilder($this->createTransactionBuilder());
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Logger\AvalaraTransactionLoggerInterface
     */
    public function createAvalaraTransactionLogger(): AvalaraTransactionLoggerInterface
    {
        return new AvalaraTransactionLogger(
            $this->getEntityManager(),
            $this->getStoreFacade(),
            $this->getUtilEncodingService()
        );
    }

    /**
     * @return \Avalara\TransactionBuilder
     */
    public function createTransactionBuilder(): TransactionBuilder
    {
        return new TransactionBuilder(
            $this->createTransactionClient(),
            $this->getConfig()->getCompanyCode(),
            (string)DocumentType::C_SALESORDER,
            $this->getConfig()->getCustomerCode()
        );
    }

    /**
     * @return \Avalara\AvaTaxClient
     */
    public function createTransactionClient(): AvaTaxClient
    {
        $avaTaxClient = new AvaTaxClient(
            $this->getConfig()->getApplicationName(),
            $this->getConfig()->getApplicationVersion(),
            $this->getConfig()->getMachineName(),
            $this->getConfig()->getEnvironmentName()
        );

        $avaTaxClient
            ->withLicenseKey(
                $this->getConfig()->getAccountId(),
                $this->getConfig()->getLicenseKey(),
            )
            ->withCatchExceptions(false);

        return $avaTaxClient;
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Business\Expander\AvalaraTaxSetExpanderInterface
     */
    public function createAvalaraTaxSetExpander(): AvalaraTaxSetExpanderInterface
    {
        return new AvalaraTaxSetExpander($this->getRepository());
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeInterface
     */
    public function getMoneyFacade(): AvalaraTaxToMoneyFacadeInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::FACADE_MONEY);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStoreFacadeInterface
     */
    public function getStoreFacade(): AvalaraTaxToStoreFacadeInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::FACADE_STORE);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): AvalaraTaxToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestExpanderPluginInterface[]
     */
    public function getCreateTransactionRequestExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AvalaraTaxDependencyProvider::PLUGINS_CREATE_TRANSACTION_REQUEST_EXPANDER);
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only. Use $this->createProductOptionTaxRateWithItemShipmentTaxRateCalculator() instead.
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
