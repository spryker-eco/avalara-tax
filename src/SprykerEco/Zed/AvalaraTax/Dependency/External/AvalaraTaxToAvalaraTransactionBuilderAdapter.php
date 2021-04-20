<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Dependency\External;

use Avalara\TransactionBuilder;
use SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig;
use stdClass;

class AvalaraTaxToAvalaraTransactionBuilderAdapter implements AvalaraTaxToTransactionBuilderInterface
{
    /**
     * @var \Avalara\TransactionBuilder
     */
    protected $transactionBuilder;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface $avalaraTaxClient
     * @param \SprykerEco\Zed\AvalaraTax\AvalaraTaxConfig $avalaraTaxConfig
     */
    public function __construct(
        AvalaraTaxToAvalaraTaxClientInterface $avalaraTaxClient,
        AvalaraTaxConfig $avalaraTaxConfig
    ) {
        $this->transactionBuilder = new TransactionBuilder(
            $avalaraTaxClient,
            $avalaraTaxConfig->getCompanyCode(),
            $avalaraTaxConfig->getBeforeOrderPlacedTransactionTypeId(),
            $avalaraTaxConfig->getDefaultCustomerCode()
        );
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function withType(string $type)
    {
        $this->transactionBuilder->withType($type);

        return $this;
    }

    /**
     * @param string $currencyCode
     *
     * @return $this
     */
    public function withCurrencyCode(string $currencyCode)
    {
        $this->transactionBuilder->withCurrencyCode($currencyCode);

        return $this;
    }

    /**
     * @return $this
     */
    public function withCommit()
    {
        $this->transactionBuilder->withCommit();

        return $this;
    }

    /**
     * @param string $no
     *
     * @return $this
     */
    public function withPurchaseOrderNo(string $no)
    {
        $this->transactionBuilder->withPurchaseOrderNo($no);

        return $this;
    }

    /**
     * @param float $amount
     * @param float $quantity
     * @param string $itemCode
     * @param string $taxCode
     * @param string|null $lineNumber
     *
     * @return $this
     */
    public function withLine(float $amount, float $quantity, string $itemCode, string $taxCode, ?string $lineNumber = null)
    {
        $this->transactionBuilder->withLine($amount, $quantity, $itemCode, $taxCode, $lineNumber);

        return $this;
    }

    /**
     * @param string $ref1
     * @param string|null $ref2
     *
     * @return $this
     */
    public function withLineCustomFields(string $ref1, ?string $ref2 = null)
    {
        $this->transactionBuilder->withLineCustomFields($ref1, $ref2);

        return $this;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function withLineDescription(string $description)
    {
        $this->transactionBuilder->withLineDescription($description);

        return $this;
    }

    /**
     * @return $this
     */
    public function withLineTaxIncluded()
    {
        $this->transactionBuilder->withLineTaxIncluded();

        return $this;
    }

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
    public function withAddress(string $type, string $line1, string $line2, string $line3, string $city, string $postalCode, string $country, ?string $region)
    {
        $this->transactionBuilder->withAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country);

        return $this;
    }

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
    ) {
        $this->transactionBuilder->withLineAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country);

        return $this;
    }

    /**
     * @param string|null $include
     *
     * @return \stdClass|\Avalara\TransactionModel
     */
    public function create(?string $include = null): stdClass
    {
        return $this->transactionBuilder->create($include);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this->transactionBuilder;
    }
}
