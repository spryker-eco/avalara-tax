<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\External;

use Avalara\AvaTaxClient;
use stdClass;

class AvalaraTaxToAvalaraAvaTaxClientAdapter implements AvalaraTaxToAvalaraTaxClientInterface
{
    /**
     * @var \Avalara\AvaTaxClient
     */
    protected $avaTaxClient;

    /**
     * @param string $applicationName
     * @param string $applicationVersion
     * @param string $environment
     * @param string $machineName
     * @param array $guzzleParams
     */
    public function __construct(
        string $applicationName,
        string $applicationVersion,
        string $environment,
        string $machineName = '',
        array $guzzleParams = []
    ) {
        $this->avaTaxClient = new AvaTaxClient(
            $applicationName,
            $applicationVersion,
            $environment,
            $machineName,
            $guzzleParams
        );
    }

    /**
     * @param int $accountId
     * @param string $licenseKey
     *
     * @return $this
     */
    public function withLicenseKey(int $accountId, string $licenseKey)
    {
        $this->avaTaxClient->withLicenseKey($accountId, $licenseKey);

        return $this;
    }

    /**
     * @param bool $catchExceptions
     *
     * @return $this
     */
    public function withCatchExceptions(bool $catchExceptions = true)
    {
        $this->avaTaxClient->withCatchExceptions($catchExceptions);

        return $this;
    }

    /**
     * @param \stdClass|\Avalara\CreateTransactionModel $createTransactionModel
     * @param string|null $include
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function createTransaction(stdClass $createTransactionModel, ?string $include = null): stdClass
    {
        return $this->avaTaxClient->createTransaction($include, $createTransactionModel);
    }
}
