<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Expander;

use ArrayObject;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface;

class AvalaraTaxCodeExpander implements AvalaraTaxCodeExpanderInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface
     */
    protected $avalaraTaxRepository;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Persistence\AvalaraTaxRepositoryInterface $avalaraTaxRepository
     */
    public function __construct(AvalaraTaxRepositoryInterface $avalaraTaxRepository)
    {
        $this->avalaraTaxRepository = $avalaraTaxRepository;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function expandProductConcreteWithAvalaraTaxCode(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer
    {
        $productAbstractAvalaraTaxCode = $this->avalaraTaxRepository->findProductAbstractAvalaraTaxCode($productConcreteTransfer->getFkProductAbstractOrFail());

        return $productConcreteTransfer->setAvalaraTaxCode($productAbstractAvalaraTaxCode);
    }

    /**
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function expandCartItemsWithAvalaraTaxCode(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        $productConcreteSkus = $this->extractProductConcreteSkuFromItemTransfers($cartChangeTransfer->getItems());
        $productConcreteAvalaraTaxCodesIndexedBySku = $this->avalaraTaxRepository->getProductConcreteAvalaraTaxCodesBySkus($productConcreteSkus);

        foreach ($cartChangeTransfer->getItems() as $itemTransfer) {
            if (!isset($productConcreteAvalaraTaxCodesIndexedBySku[$itemTransfer->getSkuOrFail()])) {
                continue;
            }

            $itemTransfer->setAvalaraTaxCode($productConcreteAvalaraTaxCodesIndexedBySku[$itemTransfer->getSkuOrFail()]);
        }

        return $cartChangeTransfer;
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return string[]
     */
    protected function extractProductConcreteSkuFromItemTransfers(ArrayObject $itemTransfers): array
    {
        return array_map(function (ItemTransfer $itemTransfer) {
            return $itemTransfer->getSkuOrFail();
        }, $itemTransfers->getArrayCopy());
    }
}
