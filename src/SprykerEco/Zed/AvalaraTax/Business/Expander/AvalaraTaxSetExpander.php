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

class AvalaraTaxSetExpander implements AvalaraTaxSetExpanderInterface
{
    /**
     * @uses \Orm\Zed\Product\Persistence\Map\SpyProductTableMap::COL_AVALARA_TAX_CODE
     */
    protected const COL_PRODUCT_AVALARA_TAX_CODE = 'spy_product.avalara_tax_code';

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
    public function expandProductConcreteTransferWithAvalaraTaxCode(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer
    {
        $productAbstractAvalaraTaxCode = $this->avalaraTaxRepository->findProductAbstractAvalaraTaxCode($productConcreteTransfer->getFkProductAbstractOrFail());

        return $productConcreteTransfer->setAvalaraTaxCode($productAbstractAvalaraTaxCode);
    }

    /**
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function expandCartItemTransfersWithAvalaraTaxCode(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        $productConcreteSkus = $this->extractProductConcreteSkuFromItemTransfers($cartChangeTransfer->getItems());
        $productConcreteAvalaraTaxCodesIndexedBySku = $this->avalaraTaxRepository->getProductConcreteAvalaraTaxCodesBySkus($productConcreteSkus);

        foreach ($cartChangeTransfer->getItems() as $itemTransfer) {
            if (!isset($productConcreteAvalaraTaxCodesIndexedBySku[$itemTransfer->getSkuOrFail()])) {
                continue;
            }

            $itemTransfer->setAvalaraTaxCode($productConcreteAvalaraTaxCodesIndexedBySku[$itemTransfer->getSkuOrFail()][static::COL_PRODUCT_AVALARA_TAX_CODE]);
        }

        return $cartChangeTransfer;
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return array
     */
    protected function extractProductConcreteSkuFromItemTransfers(ArrayObject $itemTransfers): array
    {
        return array_map(function (ItemTransfer $itemTransfer) {
            return $itemTransfer->getSkuOrFail();
        }, $itemTransfers->getArrayCopy());
    }
}
