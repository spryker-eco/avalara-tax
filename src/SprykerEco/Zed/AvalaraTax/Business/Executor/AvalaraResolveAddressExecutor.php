<?php

/**
 * MIT License
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Executor;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer;
use Generated\Shared\Transfer\AvalaraResolveAddressRequestTransfer;
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
     * @param \Generated\Shared\Transfer\AddressTransfer $addressTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    public function executeResolveAddressRequest(AddressTransfer $addressTransfer): AvalaraCreateTransactionResponseTransfer
    {
        $avalaraResolveAddressRequestTransfer = $this->avalaraResolveAddressRequestMapper->mapAddressTransferToAvalaraResolveAddressRequestTransfer(
            $addressTransfer,
            new AvalaraResolveAddressRequestTransfer()
        );

        $avalaraAddressValidationInfoTransfer = $avalaraResolveAddressRequestTransfer->getAddressOrFail();
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
        } catch (Throwable $e) {
            return (new AvalaraCreateTransactionResponseTransfer())
                ->setIsSuccessful(false)
                ->addMessage((new MessageTransfer())->setMessage($e->getMessage()));
        }

        return $this->handleAddressResolutionModel($addressResolutionModel);
    }

    /**
     * @param \stdClass|\Avalara\AddressResolutionModel $addressResolutionModel
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionResponseTransfer
     */
    protected function handleAddressResolutionModel(stdClass $addressResolutionModel): AvalaraCreateTransactionResponseTransfer
    {
        $avalaraCreateTransactionResponseTransfer = new AvalaraCreateTransactionResponseTransfer();
        if (!isset($addressResolutionModel->messages) || $addressResolutionModel->messages === []) {
            return $avalaraCreateTransactionResponseTransfer->setIsSuccessful(true);
        }

        foreach ($addressResolutionModel->messages as $avaTaxMessage) {
            $avalaraCreateTransactionResponseTransfer->addMessage(
                (new MessageTransfer())->setMessage($avaTaxMessage->summary)
            );
        }

        return $avalaraCreateTransactionResponseTransfer->setIsSuccessful(false);
    }
}
