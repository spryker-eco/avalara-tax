<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\External;

use stdClass;

interface AvalaraTaxToAvalaraTaxClientInterface
{
    /**
     * @uses \Avalara\TextCase::C_MIXED
     *
     * @var int
     */
    public const DEFAULT_ADDRESS_VALIDATION_TEXT_CASE = 1;

    /**
     * @param string|null $include
     * @param array $createTransactionModel
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function createTransaction(?string $include, array $createTransactionModel): stdClass;

    /**
     * @param string $line1
     * @param string $line2
     * @param string $city
     * @param string $postalCode
     * @param string $country
     * @param string|null $line3
     * @param string|null $region
     * @param int $textCase
     *
     * @return \stdClass|\Avalara\AddressResolutionModel
     */
    public function resolveAddress(
        string $line1,
        string $line2,
        string $city,
        string $postalCode,
        string $country,
        ?string $line3,
        ?string $region,
        int $textCase = 1
    );
}
