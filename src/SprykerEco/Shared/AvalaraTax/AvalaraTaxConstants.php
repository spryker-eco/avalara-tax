<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Shared\AvalaraTax;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface AvalaraTaxConstants
{
    /**
     * Specification:
     * - Name of the Avalara application.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_APPLICATION_NAME = 'AVALARA_TAX:APPLICATION_NAME';

    /**
     * Specification:
     * - Version of the Avalara application.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_APPLICATION_VERSION = 'AVALARA_TAX:APPLICATION_VERSION';

    /**
     * Specification:
     * - The machine name of the machine on which this code is executing.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_MACHINE_NAME = 'AVALARA_TAX:MACHINE_NAME';

    /**
     * Specification:
     * - Indicates which server to use, acceptable values are "sandbox" or "production", or the full URL of your AvaTax instance.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_ENVIRONMENT_NAME = 'AVALARA_TAX:ENVIRONMENT_NAME';

    /**
     * Specification:
     * - The account ID for your AvaTax account.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_ACCOUNT_ID = 'AVALARA_TAX:ACCOUNT_ID';

    /**
     * Specification:
     * - The private license key for your AvaTax account.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_LICENSE_KEY = 'AVALARA_TAX:LICENSE_KEY';

    /**
     * Specification:
     * - Company identifier.
     *
     * @api
     *
     * @var string
     */
    public const AVALARA_TAX_COMPANY_CODE = 'AVALARA_TAX:COMPANY_CODE';
}
