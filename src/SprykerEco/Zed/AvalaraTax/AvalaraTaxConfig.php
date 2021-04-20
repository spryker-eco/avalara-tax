<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\AvalaraTax\AvalaraTaxConstants;

class AvalaraTaxConfig extends AbstractBundleConfig
{
    /**
     * @uses \Avalara\DocumentType::C_SALESORDER
     */
    public const AVALARA_TRANSACTION_TYPE_ID_SALES_ORDER = 0;

    /**
     * @uses \Avalara\DocumentType::C_SALESINVOICE
     */
    public const AVALARA_TRANSACTION_TYPE_ID_SALES_INVOICE = 1;

    /**
     * @api
     *
     * @return string
     */
    public function getApplicationName(): string
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_APPLICATION_NAME);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getApplicationVersion(): string
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_APPLICATION_VERSION);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getMachineName(): string
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_MACHINE_NAME);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getEnvironmentName(): string
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_ENVIRONMENT_NAME);
    }

    /**
     * @api
     *
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_ACCOUNT_ID);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getLicenseKey(): string
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_LICENSE_KEY);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getCompanyCode(): string
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_COMPANY_CODE);
    }

    /**
     * @api
     *
     * @return string
     */
    public function getDefaultCustomerCode(): string
    {
        return 'TESTCUSTOMER';
    }

    /**
     * Specification:
     * - Commit the transaction for reporting after the order is placed.
     *
     * @api
     *
     * @return bool
     */
    public function getIsTransactionCommitAfterOrderPlacementEnabled(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Returns the type of document that will be used for calculating taxes before the order is placed at the stage of forming a cart.
     *
     * @api
     *
     * @return int
     */
    public function getBeforeOrderPlacedTransactionTypeId(): int
    {
        return static::AVALARA_TRANSACTION_TYPE_ID_SALES_ORDER;
    }

    /**
     * Specification:
     * - Returns the type of document that will be used for calculating taxes after the order is placed.
     *
     * @api
     *
     * @return int
     */
    public function getAfterOrderPlacedTransactionTypeId(): int
    {
        return static::AVALARA_TRANSACTION_TYPE_ID_SALES_INVOICE;
    }
}
