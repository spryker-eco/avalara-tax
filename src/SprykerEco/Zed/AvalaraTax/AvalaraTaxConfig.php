<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax;

use Spryker\Zed\Kernel\AbstractBundleConfig;
use SprykerEco\Shared\AvalaraTax\AvalaraTaxConstants;

class AvalaraTaxConfig extends AbstractBundleConfig
{
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
     * @return bool
     */
    public function getIsTransactionCommitAfterOrderPlacementEnabled(): bool
    {
        return $this->get(AvalaraTaxConstants::AVALARA_TAX_IS_TRANSACTION_COMMIT_AFTER_ORDER_PLACEMENT_ENABLED);
    }
}
