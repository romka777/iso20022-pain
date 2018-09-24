<?php

namespace Consilience\Pain001\SupplementaryData;

class PaasExtensionPaymentReturn implements ExtensionInterface
{
    protected $returnCode;
    protected $fpid;

    /**
     * Build the supplementary data envelope content fro payment returns.
     */
    public function asDom(\DOMDocument $doc) : \DOMElement
    {
        $content = $doc->createElementNS(
            'urn:fis:paas:xsd:supl.001.001.01',
            'ext:PaymentReturn'
        );

        $content->appendChild($doc->createElement(
            'ext:PaymentReturnCode',
            $this->returnCode
        ));

        $content->appendChild($doc->createElement(
            'ext:ReturnedPaymentFPID',
            $this->fpid
        ));

        return $content;
    }

    public function __construct(string $returnCode, string $fpid)
    {
        $this->returnCode = $returnCode;
        $this->fpid = $fpid;
    }
}
