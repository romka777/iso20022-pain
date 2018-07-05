<?php

namespace Consilience\Pain002\PaymentInformation;

/**
 *
 */

use Consilience\Pain002\AbastractMessage;
use Consilience\Pain002\TransactionInformation\TransactionInformationAndStatus;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Exception;

class StatusReasonInformation extends AbastractMessage
{
    protected $additionalInformation = [];
    protected $reasonCode;
    protected $reasonProprietary;

    /**
     * @inherit
     */
    protected $exportableProperties = [
        'additionalInformation',
        'reasonCode',
        'reasonProprietary',
    ];

    public static function fromElement(DOMNode $element, DOMDocument $dom)
    {
        $record = new static();

        $record->setDom($dom);

        $record->parseRootElements($element);

        return $record;
    }

    protected function parseRootElements(DOMNode $element)
    {
        $rsn = $this->getChildElement(
            $element,
            'Rsn'
        );

        if ($rsn) {
            $this->reasonCode = $this->getChildElementValue(
                $rsn,
                'Cd'
            );

            $this->reasonProprietary = $this->getChildElementValue(
                $rsn,
                'Prtry'
            );

            $addtlInfList = $this->getChildElements(
                $element,
                'AddtlInf'
            );

            if ($addtlInfList->count()) {
                foreach ($addtlInfList as $addtlInf) {
                    $this->additionalInformation[] = $addtlInf->nodeValue;
                }
            }
        }
    }
}
