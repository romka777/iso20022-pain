<?php

namespace Consilience\Pain001\SupplementaryData;

class PaasExtensionProcessingFlags implements ExtensionInterface
{
    protected $screening = false;

    protected $fraudCheck = false;
    protected $fraudCheckCode = '';
    protected $fraudCheckNarrative = '';

    protected $manualApprove = false;
    protected $manualApproveCode = '';
    protected $manualApproveNarrative = '';

    /**
     * Build the supplementary data envelope content.
     */
    public function asDom(\DOMDocument $doc) : \DOMElement
    {
        // CHECMME: the namespace should ideally be added to the document
        // root rather than to the extension here.
        // Not sure if the "ext" alias then needs to be dynamic so that
        // multiple extensions can be supported.

        $content = $doc->createElementNS(
            'urn:fis:paas:xsd:supl.001.001.01',
            'ext:ProcessingFlags'
        );

        $content->appendChild($doc->createElement(
            'ext:Screening',
            $this->screening ? 'true' : 'false'
        ));

        $content->appendChild($doc->createElement(
            'ext:FraudCheck',
            $this->fraudCheck ? 'true' : 'false'
        ));

        if ($this->fraudCheck) {
            $content->appendChild($doc->createElement(
                'ext:FraudCheckCode',
                $this->fraudCheckCode
            ));
            $content->appendChild($doc->createElement(
                'ext:FraudCheckNarrative',
                $this->fraudCheckNarrative
            ));
        }

        $content->appendChild($doc->createElement(
            'ext:ManualApprove',
            $this->manualApprove ? 'true' : 'false'
        ));

        if ($this->manualApprove) {
            $content->appendChild(
                $doc->createElement('ext:ManualApproveCode',
                $this->manualApproveCode
            ));
            $content->appendChild(
                $doc->createElement('ext:ManualApproveNarrative',
                $this->manualApproveNarrative
            ));
        }

        return $content;
    }

    public function setScreening(bool $screening)
    {
        $this->screening = $screening;
        return $this;
    }

    /**
     * The code and narrative are required only if the fraudCheck is true.
     */
    public function setFraudCheck(
        bool $fraudCheck,
        string $fraudCheckCode = null,
        string $fraudCheckNarrative = null
    ) {
        $this->fraudCheck = $fraudCheck;

        if ($fraudCheck) {
            $this->fraudCheckCode = $fraudCheckCode;
            $this->fraudCheckNarrative = $fraudCheckNarrative;
        }

        return $this;
    }

    /**
     * The code and narrative are required only if the manualApprove is true.
     */
    public function setManualApprove(
        bool $manualApprove,
        string $manualApproveCode = null,
        string $manualApproveNarrative = null
    ) {
        $this->manualApprove = $manualApprove;

        if ($manualApprove) {
            $this->manualApproveCode = $manualApproveCode;
            $this->manualApproveNarrative = $manualApproveNarrative;
        }

        return $this;
    }
}
