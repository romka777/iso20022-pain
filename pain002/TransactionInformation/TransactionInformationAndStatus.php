<?php

namespace Consilience\Pain002\TransactionInformation;

/**
 * General pain.002 message
 */

use Consilience\Pain002\AbastractMessage;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Exception;

class TransactionInformationAndStatus extends AbastractMessage
{
    protected $statusId;
    protected $originalInstructionId;
    protected $originalEndToEndId;
    protected $transactionStatus;

    protected $instructedAmount;
    protected $instructedCurrency;

    protected $requestedCollectionDate;
    protected $requestedExecutionDate;

    protected $instructionPriority;

    protected $interBankSettlementDate;
    // "INDA", "INGA", "COVE", "CLRG"
    protected $settlementMethod;

    protected $clearingSystemProprietary;
    protected $clearingSystemCode;

    public static function fromElement(DOMNode $element, DOMDocument $dom)
    {
        $record = new static();

        $record->setDom($dom);

        $record->parseRootElements($element);

        return $record;
    }

    protected function parseRootElements(DOMNode $element)
    {
        $this->statusId = $this->getChildElementValue(
            $element,
            'StsId'
        );

        $this->originalInstructionId = $this->getChildElementValue(
            $element,
            'OrgnlInstrId'
        );

        $this->originalEndToEndId = $this->getChildElementValue(
            $element,
            'OrgnlEndToEndId'
        );

        $this->transactionStatus = $this->getChildElementValue(
            $element,
            'TxSts'
        );

        // TODO: array of Status Reason Information

        $originalTransactionReference = $this->getChildElement(
            $element,
            'OrgnlTxRef'
        );

        if ($originalTransactionReference) {
            $amount = $this->getChildElement(
                $originalTransactionReference,
                'Amt'
            );

            if ($amount) {
                $instructedAmount = $this->getChildElement(
                    $amount,
                    'InstdAmt',
                    [static::ASSERT_REQUIRED]
                );

                $this->instructedAmount = $instructedAmount->nodeValue;
                $this->instructedCurrency = $instructedAmount->getAttribute('Ccy');
            }

            $this->requestedCollectionDate = $this->getChildElementValue(
                $originalTransactionReference,
                'ReqdColltnDt'
            );

            $this->requestedExecutionDate = $this->getChildElementValue(
                $originalTransactionReference,
                'ReqdExctnDt'
            );

            $this->interBankSettlementDate = $this->getChildElementValue(
                $originalTransactionReference,
                'IntrBkSttlmDt'
            );

            $SettlementInstruction = $this->getChildElement(
                $originalTransactionReference,
                'SttlmInf'
            );

            if ($SettlementInstruction) {
                $this->settlementMethod = $this->getChildElementValue(
                    $SettlementInstruction,
                    'SttlmMtd'
                );

                //ClrSys + Cd or Prtry (Code or Proprietary)
                $clearingSystem = $this->getChildElement(
                    $SettlementInstruction,
                    'ClrSys'
                );

                if ($clearingSystem) {
                    $this->clearingSystemCode = $this->getChildElementValue(
                        $clearingSystem,
                        'Cd'
                    );

                    $this->clearingSystemProprietary = $this->getChildElementValue(
                        $clearingSystem,
                        'Prtry'
                    );
                }
            }

            $paymentTypeInformation = $this->getChildElement(
                $originalTransactionReference,
                'PmtTpInf'
            );

            if ($paymentTypeInformation) {
                $this->instructionPriority = $this->getChildElementValue(
                    $paymentTypeInformation,
                    'InstrPrty'
                );
            }
        }
    }
}
