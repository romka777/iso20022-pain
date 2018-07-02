<?php

namespace Consilience\Pain002\PaymentInformation;

/**
 * General pain.002 message
 */

use Consilience\Pain002\AbastractMessage;
use Consilience\Pain002\TransactionInformation\TransactionInformationAndStatus;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Exception;

class OriginalPaymentInformationAndStatus extends AbastractMessage
{
    protected $originalPaymentId;
    protected $originalNumberOfTransactions;
    protected $originalControlSum;

    protected $transactionInformationAndStatus = [];

    protected $paymentStatus;

    protected $statusReasonInformation = [];

    public static function fromElement(DOMNode $element, DOMDocument $dom)
    {
        $record = new static();

        $record->setDom($dom);

        $record->parseRootElements($element);

        // Multiple Transaction Information and Status <TxInfAndSts>

        $record->parseTransactionInformationAndStatus($element);

        return $record;
    }

    protected function parseRootElements(DOMNode $element)
    {
        $this->originalPaymentId = $this->getChildElementValue(
            $element,
            'OrgnlPmtInfId',
            [static::ASSERT_REQUIRED]
        );

        $this->originalNumberOfTransactions = $this->getChildElementValue(
            $element,
            'OrgnlNbOfTxs'
        );

        $this->originalControlSum = $this->getChildElementValue(
            $element,
            'OrgnlCtrlSum'
        );

        $this->paymentStatus = $this->getChildElementValue(
            $element,
            'PmtInfSts'
        );

        $statusReasonList = $this->getChildElements(
            $element,
            'StsRsnInf'
        );

        if ($statusReasonList->count()) {
            // Optional Orgtr-> sequence of optional addresses and contact details (not implemented)
            // Optional Rsn + (Cd or Prtry strings)
            // List of AddtlInf (strings)

            foreach ($statusReasonList as $statusReason) {
                $this->statusReasonInformation[] = StatusReasonInformation::fromElement(
                    $statusReason,
                    $this->dom
                );
            }
        }
    }

    protected function parseTransactionInformationAndStatus(DOMNode $element)
    {
        $transactionInformationAndStatusList = $this->getChildElements(
            $element,
            'TxInfAndSts'
        );

        // No records supplied.

        if ($transactionInformationAndStatusList->count() === 0) {
            return;
        }

        // Parse each record.

        foreach ($transactionInformationAndStatusList as $transactionInformationAndStatus) {
            // Instantiate then parse.

            $record = TransactionInformationAndStatus::fromElement(
                $transactionInformationAndStatus,
                $this->dom
            );

            $this->transactionInformationAndStatus[] = $record;
        }
    }
}
