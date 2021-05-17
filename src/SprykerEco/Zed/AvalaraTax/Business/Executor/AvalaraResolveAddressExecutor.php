<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Generated\Shared\Transfer\AvalaraAddressValidationInfoTransfer;
use Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer;
use Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraResolveAddressRequestMapperInterface;
use SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface;
use stdClass;
use Throwable;

class AvalaraResolveAddressExecutor implements AvalaraResolveAddressExecutorInterface
{
    /**
     * @var \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraResolveAddressRequestMapperInterface
     */
    protected $avalaraResolveAddressRequestMapper;

    /**
     * @var \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface
     */
    protected $avalaraTaxClient;

    /**
     * @param \SprykerEco\Zed\AvalaraTax\Business\Mapper\AvalaraResolveAddressRequestMapperInterface $avalaraResolveAddressRequestMapper
     * @param \SprykerEco\Zed\AvalaraTax\Dependency\External\AvalaraTaxToAvalaraTaxClientInterface $avalaraTaxClient
     */
    public function __construct(
        AvalaraResolveAddressRequestMapperInterface $avalaraResolveAddressRequestMapper,
        AvalaraTaxToAvalaraTaxClientInterface $avalaraTaxClient
    ) {
        $this->avalaraResolveAddressRequestMapper = $avalaraResolveAddressRequestMapper;
        $this->avalaraTaxClient = $avalaraTaxClient;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer[] $addressTransfers
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer
     */
    public function executeResolveAddressRequest(array $addressTransfers): AvalaraResolveAddressResponseTransfer
    {
        $avalaraResolveAddressRequestTransfer = $this->avalaraResolveAddressRequestMapper->mapAddressTransfersToAvalaraResolveAddressRequestTransfer(
            $addressTransfers,
            new AvalaraResolveAddressRequestTransfer()
        );

        $avalaraResolveAddressResponseTransfer = (new AvalaraResolveAddressResponseTransfer())->setIsSuccessful(true);

        foreach ($avalaraResolveAddressRequestTransfer->getAddresses() as $avalaraAddressValidationInfoTransfer) {
            $avalaraResolveAddressResponseTransfer = $this->executeAddressValidationRequest($avalaraAddressValidationInfoTransfer, $avalaraResolveAddressResponseTransfer);
        }

        return $avalaraResolveAddressResponseTransfer;
    }

    /**
     * @param \stdClass|\Avalara\AddressResolutionModel $addressResolutionModel
     * @param \Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer $avalaraResolveAddressResponseTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer
     */
    protected function handleAddressResolutionModel(
        stdClass $addressResolutionModel,
        AvalaraResolveAddressResponseTransfer $avalaraResolveAddressResponseTransfer
    ): AvalaraResolveAddressResponseTransfer {
        if (!isset($addressResolutionModel->messages) || $addressResolutionModel->messages === []) {
            return $avalaraResolveAddressResponseTransfer;
        }

        foreach ($addressResolutionModel->messages as $avaTaxMessage) {
            $avalaraResolveAddressResponseTransfer->addMessage(
                (new MessageTransfer())->setMessage($avaTaxMessage->summary)
            );
        }

        return $avalaraResolveAddressResponseTransfer->setIsSuccessful(false);
    }

    /**
     * @param \Generated\Shared\Transfer\AvalaraAddressValidationInfoTransfer $avalaraAddressValidationInfoTransfer
     * @param \Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer $avalaraResolveAddressResponseTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraResolveAddressResponseTransfer
     */
    protected function executeAddressValidationRequest(
        AvalaraAddressValidationInfoTransfer $avalaraAddressValidationInfoTransfer,
        AvalaraResolveAddressResponseTransfer $avalaraResolveAddressResponseTransfer
    ): AvalaraResolveAddressResponseTransfer {
        try {
            $addressResolutionModel = $this->avalaraTaxClient->resolveAddress(
                $avalaraAddressValidationInfoTransfer->getLine1OrFail(),
                $avalaraAddressValidationInfoTransfer->getLine2OrFail(),
                $avalaraAddressValidationInfoTransfer->getCityOrFail(),
                $avalaraAddressValidationInfoTransfer->getPostalCodeOrFail(),
                $avalaraAddressValidationInfoTransfer->getCountryOrFail(),
                $avalaraAddressValidationInfoTransfer->getLine3(),
                $avalaraAddressValidationInfoTransfer->getRegion()
            );

            $avalaraResolveAddressResponseTransfer = $this->handleAddressResolutionModel(
                $addressResolutionModel,
                $avalaraResolveAddressResponseTransfer
            );
        } catch (Throwable $e) {
            $avalaraResolveAddressResponseTransfer
                ->setIsSuccessful(false)
                ->addMessage((new MessageTransfer())->setValue($e->getMessage()));
        }

        return $avalaraResolveAddressResponseTransfer;
    }
}
