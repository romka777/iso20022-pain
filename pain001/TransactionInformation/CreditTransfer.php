<?php

namespace Consilience\Pain001\TransactionInformation;

use Money\Money;
use Consilience\Pain001\PaymentInformation\PaymentInformation;
use Consilience\Pain001\PostalAddressInterface;
use Consilience\Pain001\Text;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Consilience\Pain001\TransactionInformation\PurposeInterface;
use Consilience\Pain001\PaymentInformation\CategoryPurposeInterface;

/**
 * CreditTransfer contains all the information about the beneficiary and further information about the transaction.
 */
abstract class CreditTransfer
{
    /**
     * @var string
     */
    protected $instructionId;

    /**
     * @var string
     */
    protected $endToEndId;

    /**
     * @var string
     */
    protected $creditorName;

    /**
     * @var PostalAddressInterface
     */
    protected $creditorAddress;

    /**
     * @var Money
     */
    protected $amount;

    /**
     * @var string|null
     */
    protected $localInstrument;

    /**
     * @var string|null
     */
    protected $serviceLevel;

    /**
     * @var PurposeCode|null
     */
    protected $purpose;

    /**
     * @var CategoryPurposeInterface|null
     */
    protected $categoryPurpose;

    /**
     * @var string|null
     */
    protected $remittanceInformation;

    /**
     * @var string|null
     */
    protected $creditorReference;

    /**
     * @var string|null
     */
    protected $regulatoryReportingDetailsInformation;

    /**
     * Constructor
     *
     * @param string                 $instructionId   Identifier of the instruction (should be unique within the message)
     * @param string                 $endToEndId      End-To-End Identifier of the instruction (passed unchanged along the complete processing chain)
     * @param Money                  $amount          Amount of money to be transferred
     * @param string                 $creditorName    Name of the creditor
     * @param PostalAddressInterface $creditorAddress Address of the creditor
     *
     * @throws \InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     */
    public function __construct(
        $instructionId,
        $endToEndId,
        Money $amount,
        $creditorName,
        PostalAddressInterface $creditorAddress
    ) {
        $this->instructionId = Text::assertIdentifier($instructionId);
        $this->endToEndId = Text::assertIdentifier($endToEndId);
        $this->amount = $amount;
        $this->creditorName = Text::assertOptional(
            $creditorName !== '' ? $creditorName : null,
            70
        );
        $this->creditorAddress = $creditorAddress;
    }

    /**
     * Gets the local instrument
     *
     * @return string|null The local instrument
     */
    public function getLocalInstrument()
    {
        return $this->localInstrument;
    }

    /**
     * Sets the local instrument
     *
     * @return $this
     */
    public function setLocalInstrument($value)
    {
        $this->localInstrument = $value;
        return $this;
    }

    /**
     * Gets the service level
     *
     * @return string|null The service level
     */
    public function getServiceLevel()
    {
        return $this->serviceLevel;
    }

    /**
     * Sets the purpose of the payment
     *
     * @param PurposeInterface $purpose The purpose
     *
     * @return CreditTransfer This credit transfer
     */
    public function setPurpose(PurposeInterface $purpose)
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function setCategoryPurpose(CategoryPurposeInterface $categoryPurpose)
    {
        $this->categoryPurpose = $categoryPurpose;

        return $this;
    }

    /**
     * Sets the unstructured remittance information.
     *
     * @param string|null $remittanceInformation
     *
     * @return CreditTransfer This credit transfer
     *
     * @throws \InvalidArgumentException When the information contains invalid characters or is too long.
     */
    public function setRemittanceInformation($remittanceInformation)
    {
        $this->remittanceInformation = Text::assertOptional($remittanceInformation, 140);

        return $this;
    }

    /**
     * Sets the creditor reference for the structured remittance information.
     *
     * @param string|null $creditorReference
     *
     * @return CreditTransfer This credit transfer
     *
     * @throws \InvalidArgumentException When the information contains invalid characters or is too long.
     */
    public function setCreditorReference($creditorReference)
    {
        $this->creditorReference = Text::assertOptional($creditorReference, 35);

        return $this;
    }

    /**
     * Gets the instructed amount of this transaction
     *
     * @return Money The instructed amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setRegulatoryReportingDetailsInformation(
        $regulatoryReportingDetailsInformation
    ) {
        $this->regulatoryReportingDetailsInformation = Text::assertOptional(
            $regulatoryReportingDetailsInformation,
            35
        );

        return $this;
    }

    /**
     * Builds a DOM tree of this transaction
     *
     * @param \DOMDocument       $doc
     * @param PaymentInformation $paymentInformation Information on B-level
     *
     * @return \DOMElement The built DOM tree
     */
    abstract public function asDom(
        \DOMDocument $doc,
        PaymentInformation $paymentInformation
    );

    /**
     * Builds a DOM tree of this transaction and adds header nodes
     *
     * @param \DOMDocument       $doc
     * @param PaymentInformation $paymentInformation The corresponding B-level element
     *
     * @return \DOMNode The built DOM node
     */
    protected function buildHeader(\DOMDocument $doc, PaymentInformation $paymentInformation)
    {
        $root = $doc->createElement('CdtTrfTxInf');

        $id = $doc->createElement('PmtId');
        $id->appendChild(Text::xml($doc, 'InstrId', $this->instructionId));
        $id->appendChild(Text::xml($doc, 'EndToEndId', $this->endToEndId));
        $root->appendChild($id);

        if (
            (! $paymentInformation->hasPaymentTypeInformation()
            && ($this->localInstrument !== null || $this->serviceLevel !== null))
            || $this->categoryPurpose !== null
        ) {
            $paymentTypeInfo = $doc->createElement('PmtTpInf');

            if ($this->localInstrument !== null) {
                $localInstrumentNode = $doc->createElement('LclInstrm');
                $localInstrumentNode->appendChild($doc->createElement('Prtry', $this->localInstrument));
                $paymentTypeInfo->appendChild($localInstrumentNode);
            }

            if ($this->serviceLevel !== null) {
                $serviceLevelNode = $doc->createElement('SvcLvl');
                $serviceLevelNode->appendChild($doc->createElement('Cd', $this->serviceLevel));
                $paymentTypeInfo->appendChild($serviceLevelNode);
            }

            // Append the optional Category Purpose

            if ($this->categoryPurpose !== null) {
                $categoryPurposeNode = $doc->createElement('CtgyPurp');
                $categoryPurposeNode->appendChild($this->categoryPurpose->asDom($doc));
                $paymentTypeInfo->appendChild($categoryPurposeNode);
            }

            $root->appendChild($paymentTypeInfo);
        }

        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        $amount = $doc->createElement('Amt');
        $instdAmount = $doc->createElement('InstdAmt', $moneyFormatter->format($this->amount));
        $instdAmount->setAttribute('Ccy', $this->amount->getCurrency()->getCode());
        $amount->appendChild($instdAmount);
        $root->appendChild($amount);

        // This is a nasty short-cut to put in one Regulatory Reporting
        // text field.

        if ($this->regulatoryReportingDetailsInformation !== null) {
            $regulatoryReporting = $doc->createElement('RgltryRptg');

            $details = $doc->createElement('Dtls');
            $regulatoryReporting->appendChild($details);

            $information = $doc->createElement('Inf', $this->regulatoryReportingDetailsInformation);
            $details->appendChild($information);

            $root->appendChild($regulatoryReporting);
        }

        return $root;
    }

    /**
     * Builds a DOM node of the Creditor field
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMNode The built DOM node
     */
    protected function buildCreditor(\DOMDocument $doc)
    {
        $creditor = $doc->createElement('Cdtr');

        // The name is optional.

        if ($this->creditorName !== null) {
            $creditor->appendChild(Text::xml($doc, 'Nm', $this->creditorName));
        }

        // The postal address is optional.

        if ($this->creditorAddress !== null) {
            $creditor->appendChild($this->creditorAddress->asDom($doc));
        }

        return $creditor;
    }

    /**
     * Appends the purpose to the transaction
     *
     * @param \DOMDocument $doc
     * @param \DOMElement  $transaction
     */
    protected function appendPurpose(\DOMDocument $doc, \DOMElement $transaction)
    {
        if ($this->purpose !== null) {
            $purposeNode = $doc->createElement('Purp');
            $purposeNode->appendChild($this->purpose->asDom($doc));
            $transaction->appendChild($purposeNode);
        }
    }

    /**
     * Appends the remittance information to the transaction.
     *
     * @param \DOMDocument $doc
     * @param \DOMElement  $transaction
     */
    protected function appendRemittanceInformation(
        \DOMDocument $doc,
        \DOMElement $transaction
    ) {
        if (empty($this->remittanceInformation) && empty($this->creditorReference)) {
            return;
        }

        $remittanceNode = $doc->createElement('RmtInf');

        // Unstructured text remittanceInformation.
        // In reality this element can be repeated an unbounce number
        // of times, but that's not supported here.

        if (! empty($this->remittanceInformation)) {
            $remittanceNode->appendChild(Text::xml($doc, 'Ustrd', $this->remittanceInformation));
            $transaction->appendChild($remittanceNode);
        }

        // Just one element of the structured remittanceInformation.
        // This is needed for UK domestic payments at least.

        if (! empty($this->creditorReference)) {
            $structured = $doc->createElement('Strd');
            $remittanceNode->appendChild($structured);

            $creditorReferenceInformation = $doc->createElement('CdtrRefInf');
            $structured->appendChild($creditorReferenceInformation);

            $creditorReferenceInformation->appendChild(
                $doc->createElement('Ref', $this->creditorReference)
            );

            $transaction->appendChild($remittanceNode);
        }
    }
}
