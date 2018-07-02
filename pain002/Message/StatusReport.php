<?php

namespace Consilience\Pain002\Message;

use Consilience\Pain001\Money\Mixed;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Exception;

/**
 * General pain.002 message
 */
class StatusReport extends AbastractMessage
{
    /**
     * Group Header
     */

    // MsgId e.g. "API0000296844600"
    protected $messageId;
    // CreDtTm e.g."2018-06-29T14:13:11+00:00"
    protected $creationDateTime;

    /**
     * Original Group Information And Status
     */

    protected $originalMessageId;
    // e.g. "pain.001.001.06"
    protected $originalMessageNameId;
    protected $originalNumberOfTransactions;
    protected $originalCreationDateTime;
    protected $originalControlSum;
    protected $groupStatus;

    /**
     * Parse an XML string.
     *
     * @partam string $xml
     * @return self
     */
    static public function parseXml(string $xml)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        $report = new static();

        try {
            $dom->loadXml($xml);

            // Save the dom.

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

            $originalPaymentInformationAndStatus = $report->getChildElements(
                $customerPaymentStatusReport,
                'OrgnlPmtInfAndSts'
            );

            dump($report);
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
}
