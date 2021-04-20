<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\External;

use Avalara\AvaTaxClient;
use SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig;
use stdClass;

class AvalaraTaxToAvalaraAvaTaxClientAdapter implements AvalaraTaxToAvalaraTaxClientInterface
{
    /**
     * @var \Avalara\AvaTaxClient
     */
    protected $avaTaxClient;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig $avalaraTaxConfig
     */
    public function __construct(AvalaraTaxConfig $avalaraTaxConfig)
    {
        $this->avaTaxClient = new AvaTaxClient(
            $avalaraTaxConfig->getApplicationName(),
            $avalaraTaxConfig->getApplicationVersion(),
            $avalaraTaxConfig->getMachineName(),
            $avalaraTaxConfig->getEnvironmentName()
        );

        $this->avaTaxClient
            ->withLicenseKey($avalaraTaxConfig->getAccountId(), $avalaraTaxConfig->getLicenseKey())
            ->withCatchExceptions(false);
    }

    /**
     * @param string|null $include
     * @param array $createTransactionModel
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function createTransaction(?string $include = null, array $createTransactionModel): stdClass
    {
        return $this->avaTaxClient->createTransaction($include, $createTransactionModel);
    }
}
