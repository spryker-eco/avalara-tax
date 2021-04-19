<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\External;

use stdClass;

interface AvalaraTaxToTransactionBuilderInterface
{
    /**
     * @param string $type
     *
     * @return $this
     */
    public function withType(string $type);

    /**
     * @param string $currencyCode
     *
     * @return $this
     */
    public function withCurrencyCode(string $currencyCode);

    /**
     * @return $this
     */
    public function withCommit();

    /**
     * @param string $no
     *
     * @return $this
     */
    public function withPurchaseOrderNo(string $no);

    /**
     * @param float $amount
     * @param float $quantity
     * @param string $itemCode
     * @param string $taxCode
     * @param string|null $lineNumber
     *
     * @return $this
     */
    public function withLine(float $amount, float $quantity, string $itemCode, string $taxCode, ?string $lineNumber = null);

    /**
     * @param string $ref1
     * @param string|null $ref2
     *
     * @return $this
     */
    public function withLineCustomFields(string $ref1, ?string $ref2 = null);

    /**
     * @param string $description
     *
     * @return $this
     */
    public function withLineDescription(string $description);

    /**
     * @return $this
     */
    public function withLineTaxIncluded();

    /**
     * @param string $type
     * @param string $line1
     * @param string $line2
     * @param string $line3
     * @param string $city
     * @param string $postalCode
     * @param string $country
     * @param string|null $region
     *
     * @return $this
     */
    public function withAddress(string $type, string $line1, string $line2, string $line3, string $city, string $postalCode, string $country, ?string $region);

    /**
     * @param string $type
     * @param string $line1
     * @param string $line2
     * @param string $line3
     * @param string $city
     * @param string $postalCode
     * @param string $country
     * @param string|null $region
     *
     * @return $this
     */
    public function withLineAddress(
        string $type,
        string $line1,
        string $line2,
        string $line3,
        string $city,
        string $postalCode,
        string $country,
        ?string $region
    );

    /**
     * @param string|null $include
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function create(?string $include = null): stdClass;
}
