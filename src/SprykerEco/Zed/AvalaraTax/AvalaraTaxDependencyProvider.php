<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax;

use Orm\Zed\Product\Persistence\SpyProductAbstractQuery;
use Orm\Zed\Product\Persistence\SpyProductQuery;
use Orm\Zed\Stock\Persistence\SpyStockProductQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraAvaTaxClientAdapter;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTransactionBuilderAdapter;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToMoneyFacadeBridge;
use SprykerEco\Zed\AvalaraTax\Dependency\Facade\AvalaraTaxToStockFacadeBridge;
use SprykerEco\Zed\AvalaraTax\Dependency\Service\AvalaraTaxToUtilEncodingServiceBridge;

/**
 * @method \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig getConfig()
 */
class AvalaraTaxDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_MONEY = 'FACADE_MONEY';

    /**
     * @var string
     */
    public const FACADE_STOCK = 'FACADE_STOCK';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const PROPEL_QUERY_PRODUCT_ABSTRACT = 'PROPEL_QUERY_PRODUCT_ABSTRACT';

    /**
     * @var string
     */
    public const PROPEL_QUERY_PRODUCT = 'PROPEL_QUERY_PRODUCT';

    /**
     * @var string
     */
    public const PROPEL_QUERY_STOCK_PRODUCT = 'PROPEL_QUERY_STOCK_PRODUCT';

    /**
     * @var string
     */
    public const PLUGINS_CREATE_TRANSACTION_REQUEST_EXPANDER = 'PLUGINS_CREATE_TRANSACTION_REQUEST_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_CREATE_TRANSACTION_REQUEST_AFTER = 'PLUGINS_CREATE_TRANSACTION_REQUEST_AFTER';

    /**
     * @var string
     */
    public const AVALARA_TAX_CLIENT = 'AVALARA_TAX_CLIENT';

    /**
     * @var string
     */
    public const AVALARA_TRANSACTION_BUILDER = 'AVALARA_TRANSACTION_BUILDER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addMoneyFacade($container);
        $container = $this->addStockFacade($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addAvalaraTaxClient($container);
        $container = $this->addAvalaraTransactionBuilder($container);
        $container = $this->addCreateTransactionRequestExpanderPlugins($container);
        $container = $this->addCreateTransactionRequestAfterPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);

        $container = $this->addProductAbstractPropelQuery($container);
        $container = $this->addProductPropelQuery($container);
        $container = $this->addStockProductPropelQuery($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMoneyFacade(Container $container): Container
    {
        $container->set(static::FACADE_MONEY, function (Container $container) {
            return new AvalaraTaxToMoneyFacadeBridge($container->getLocator()->money()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStockFacade(Container $container): Container
    {
        $container->set(static::FACADE_STOCK, function (Container $container) {
            return new AvalaraTaxToStockFacadeBridge($container->getLocator()->stock()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new AvalaraTaxToUtilEncodingServiceBridge($container->getLocator()->utilEncoding()->service());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductAbstractPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_PRODUCT_ABSTRACT, $container->factory(function () {
            return SpyProductAbstractQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addProductPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_PRODUCT, $container->factory(function () {
            return SpyProductQuery::create();
        }));

        return $container;
    }

    /**
     * @module Stock
     *
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStockProductPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_STOCK_PRODUCT, $container->factory(function () {
            return SpyStockProductQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addAvalaraTaxClient(Container $container): Container
    {
        $container->set(static::AVALARA_TAX_CLIENT, function () {
            return new AvalaraTaxToAvalaraAvaTaxClientAdapter($this->getConfig());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addAvalaraTransactionBuilder(Container $container): Container
    {
        $container->set(static::AVALARA_TRANSACTION_BUILDER, function (Container $container) {
            return new AvalaraTaxToAvalaraTransactionBuilderAdapter(
                $container->get(static::AVALARA_TAX_CLIENT),
                $this->getConfig(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCreateTransactionRequestExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CREATE_TRANSACTION_REQUEST_EXPANDER, function () {
            return $this->getCreateTransactionRequestExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCreateTransactionRequestAfterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CREATE_TRANSACTION_REQUEST_AFTER, function () {
            return $this->getCreateTransactionRequestAfterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestExpanderPluginInterface>
     */
    protected function getCreateTransactionRequestExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\SprykerEco\Zed\AvalaraTaxExtension\Dependency\Plugin\CreateTransactionRequestAfterPluginInterface>
     */
    protected function getCreateTransactionRequestAfterPlugins(): array
    {
        return [];
    }
}
