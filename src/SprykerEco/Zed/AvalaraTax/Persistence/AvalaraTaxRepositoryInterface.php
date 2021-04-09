<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Persistence;

interface AvalaraTaxRepositoryInterface
{
    /**
     * @param int $idProductAbstract
     *
     * @return string|null
     */
    public function findProductAbstractAvalaraTaxCode(int $idProductAbstract): ?string;

    /**
     * @param string[] $productConcreteSkus
     *
     * @return array<string, string>
     */
    public function getProductConcreteAvalaraTaxCodesBySkus(array $productConcreteSkus): array;
}
