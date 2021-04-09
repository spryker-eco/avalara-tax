<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerEco\Zed\AvalaraTax\Business\Mapper;

use Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer;
use Generated\Shared\Transfer\CalculableObjectTransfer;

interface AvalaraTransactionRequestMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     * @param \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AvalaraCreateTransactionRequestTransfer
     */
    public function mapCalculableObjectTransferToAvalaraCreateTransactionRequestTransfer(
        CalculableObjectTransfer $calculableObjectTransfer,
        AvalaraCreateTransactionRequestTransfer $avalaraCreateTransactionRequestTransfer
    ): AvalaraCreateTransactionRequestTransfer;
}
