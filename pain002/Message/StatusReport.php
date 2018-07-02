<?php

namespace Consilience\Pain002\Message;

/**
 * General pain.002 message
 */

use Consilience\Pain001\Money\Mixed;
use Consilience\Pain002\AbastractMessage;
use Consilience\Pain002\PaymentInformation\OriginalPaymentInformationAndStatus;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Exception;

class StatusReport extends AbastractMessage
{
    /**
     * Group Header.
     * Contains details about the pain.002 file itself.
     */

    // MsgId e.g. "API0000296844600"
    protected $messageId;
    // CreDtTm e.g."2018-06-29T14:13:11+00:00"
    protected $creationDateTime;

    /**
     * Original Group Information And Status.
     * Contains data from the original message.
     */

    protected $originalMessageId;
    // e.g. "pain.001.001.06"
    protected $originalMessageNameId;
    protected $originalNumberOfTransactions;
    protected $originalCreationDateTime;
    protected $originalControlSum;
    protected $groupStatus;

    /**
     * Original Payment Information and Status.
     * For each rejected batch/PI from the original message, a separate
     * OriginalPaymentInformationAndStatus is available.
     */

    protected $originalPaymentInformationAndStatus = [];

    /**
     * Instantiate from an XML string.
     *
     * @partam string $xml
     * @return self
     */
    static public function fromXml(string $xml)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        // An exception will be thrown on a failure to
        // parse the string as XML.

        $dom->loadXml($xml);

        return static::fromDom($dom);
    }

    /**
     * Instantiate from an XML DOM.
     *
     * @partam DOMDocument $dom
     * @return self
     */
    static public function fromDom(DOMDocument $dom)
    {
        $report = new static();

        try {
            // Save the dom in the report.

            $report->setDom($dom);

            $rootNode = $report->xpath->query('/xmlns:Document')->item(0);

            $rootNode = $report->getChildElement(
                $dom,
                'Document',
                [static::ASSERT_REQUIRED]
            );

            if (!$rootNode) {
                return $report->withFailure('Missing root xmlns:Document element.');
            }

            // Single wrapper for the report.
            // This is the point that other message types would diverge,
            // so there is a range of elements at this level for different
            // pain messages.
            // TODO: This section can be moved to a "expect element name" method.

            $rootChildren = $report->getChildElements($rootNode);

            if ($rootChildren->count() !== 1) {
                return $report->withFailure(sprintf(
                    'Root element xmlns:Document must contain one element. %d found',
                    $rootChildren->count()
                ));
            }

            $customerPaymentStatusReport = $rootChildren->item(0);

            // Make sure it is the node we are expecting.

            if ($customerPaymentStatusReport->nodeName !== 'CstmrPmtStsRpt') {
                return $report->withFailure(sprintf(
                    'Expected element CstmrPmtStsRpt, but found %s instead',
                    $customerPaymentStatusReport->nodeName
                ));
            }

            // A single mandatory group header.

            $report->parseGroupHeader($customerPaymentStatusReport);

            // A single mandatory Original Group Information And Status

            $report->parseOriginalGroupInformationAndStatus($customerPaymentStatusReport);

            // Multiple optional Original Payment Information And Status elements.

            $report->parseOriginalPaymentInformationAndStatus($customerPaymentStatusReport);
        } catch (Exception $e) {
            return $report->withFailure($e->getMessage());
        }

        return $report;
    }

    protected function parseGroupHeader(DOMNode $customerPaymentStatusReport)
    {
        $groupHeader = $this->getChildElement(
            $customerPaymentStatusReport,
            'GrpHdr',
            [static::ASSERT_REQUIRED]
        );

        $this->messageId = $this->getChildElementValue(
            $groupHeader,
            'MsgId',
            [static::ASSERT_REQUIRED]
        );

        $this->creationDateTime = $this->getChildElementValue(
            $groupHeader,
            'CreDtTm',
            [static::ASSERT_REQUIRED]
        );

        // TODO: InitiatingParty <InitgPty> and internal structures
    }

    public function parseOriginalGroupInformationAndStatus(DOMNode $customerPaymentStatusReport)
    {
        $originalGroupInformationAndStatus = $this->getChildElement(
            $customerPaymentStatusReport,
            'OrgnlGrpInfAndSts',
            [static::ASSERT_REQUIRED]
        );

        $this->originalMessageId = $this->getChildElementValue(
            $originalGroupInformationAndStatus,
            'OrgnlMsgId',
            [static::ASSERT_REQUIRED]
        );

        $this->originalMessageNameId = $this->getChildElementValue(
            $originalGroupInformationAndStatus,
            'OrgnlMsgNmId',
            [static::ASSERT_REQUIRED]
        );

        $this->originalNumberOfTransactions = $this->getChildElementValue(
            $originalGroupInformationAndStatus,
            'OrgnlNbOfTxs',
            [static::ASSERT_REQUIRED]
        );

        $this->originalCreationDateTime = $this->getChildElementValue(
            $originalGroupInformationAndStatus,
            'OrgnlCreDtTm'
        );

        $this->originalControlSum = $this->getChildElementValue(
            $originalGroupInformationAndStatus,
            'OrgnlCtrlSum'
        );

        $this->groupStatus = $this->getChildElementValue(
            $originalGroupInformationAndStatus,
            'GrpSts'
        );

        // TODO: multiple optional StatusReasonInformation <StsRsnInf>
    }

    protected function parseOriginalPaymentInformationAndStatus(DOMNode $customerPaymentStatusReport)
    {
        $originalPaymentInformationAndStatusList = $this->getChildElements(
            $customerPaymentStatusReport,
            'OrgnlPmtInfAndSts',
            []
        );

        // No records supplied.

        if ($originalPaymentInformationAndStatusList->count() === 0) {
            return;
        }

        // Parse each record.

        foreach ($originalPaymentInformationAndStatusList as $originalPaymentInformationAndStatus) {
            // Instantiate then parse.

            $record = OriginalPaymentInformationAndStatus::fromElement(
                $originalPaymentInformationAndStatus,
                $this->dom
            );

            $this->originalPaymentInformationAndStatus[] = $record;
        }
    }
}
