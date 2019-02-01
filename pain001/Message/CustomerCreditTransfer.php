<?php

namespace Consilience\Pain001\Message;

use Consilience\Pain001\Money;
use Consilience\Pain001\OrganisationIdentificationInterface;
use Consilience\Pain001\PaymentInformation\PaymentInformation;
use Consilience\Pain001\Text;
use Consilience\Pain001\SupplementaryDataInterface;

/**
 * CustomerCreditTransfer represents a Customer Credit Transfer Initiation (pain.001) message
 */
class CustomerCreditTransfer extends AbstractMessage
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $initiatingPartyName;
    /**
     * @var string
     */
    protected $initiatingPartyNameExt;

    protected $initiatingPartyId;

    /**
     * @var array
     */
    protected $payments = [];

    /**
     * @var array
     */
    protected $supplementaryData = [];

    /**
     * @var \DateTime
     */
    protected $creationTime;

    /**
     * Constructor
     *
     * @param string $id Identifier of the message (should usually be unique over a period of at least 90 days)
     * @param string $initiatingPartyName Name of the initiating party
     * @param OrganisationIdentificationInterface $initiatingPartyId Id of the initiating party
     *
     * @throws \InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     * @throws \Exception
     */
    public function __construct($id, $initiatingPartyName, OrganisationIdentificationInterface $initiatingPartyId)
    {
        $this->id = Text::assertIdentifier($id);
        if (mb_strlen($initiatingPartyName, 'UTF-8') <= 70) {
            $this->initiatingPartyName = Text::assert($initiatingPartyName, 70);
        } else {
            $this->initiatingPartyName = Text::assertPattern(mb_substr($initiatingPartyName, 0, 70, 'UTF-8'));
            $this->initiatingPartyNameExt = Text::assertPattern(mb_substr($initiatingPartyName, 70, null, 'UTF-8'));
        }

        $this->initiatingPartyId = $initiatingPartyId;
        $this->creationTime = new \DateTime();
    }

    /**
     * Manually sets the creation time
     *
     * @param \DateTime $creationTime The desired creation time
     *
     * @return CustomerCreditTransfer This message
     */
    public function setCreationTime(\DateTime $creationTime)
    {
        $this->creationTime = $creationTime;

        return $this;
    }

    /**
     * Adds a payment instruction
     *
     * @param PaymentInformation $payment The payment to be added
     *
     * @return CustomerCreditTransfer This message
     */
    public function addPayment(PaymentInformation $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Adds a supplementary data object
     *
     * @param PaymentInformation $payment The payment to be added
     *
     * @return CustomerCreditTransfer This message
     */
    public function addSupplementaryData(SupplementaryDataInterface $supplementaryData)
    {
        $this->supplementaryData[] = $supplementaryData;

        return $this;
    }

    /**
     * Gets the number of payments
     *
     * @return int Number of payments
     */
    public function getPaymentCount()
    {
        return count($this->payments);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSchemaName()
    {
        return 'urn:iso:std:iso:20022:tech:xsd:$pain.001.001.06';
        //return 'http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSchemaLocation()
    {
        return 'pain.001.001.06.xsd';
        //return 'pain.001.001.03.ch.02.xsd';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildDom(\DOMDocument $doc)
    {
        $transactionCount = 0;
        $transactionSum = new \Consilience\Pain001\Money\Mixed(0);

        foreach ($this->payments as $payment) {
            // Each payment may contain multiple transactions.
            $transactionCount += $payment->getTransactionCount();
            $transactionSum = $transactionSum->add($payment->getTransactionSum());
        }

        $root = $doc->createElement('CstmrCdtTrfInitn');
        $header = $doc->createElement('GrpHdr');
        $header->appendChild(Text::xml($doc, 'MsgId', $this->id));
        $header->appendChild(Text::xml($doc, 'CreDtTm', $this->creationTime->format('Y-m-d\TH:i:sP')));
        $header->appendChild(Text::xml($doc, 'NbOfTxs', $transactionCount));
        $header->appendChild(Text::xml($doc, 'CtrlSum', $transactionSum->format()));

        // Initiating Party
        $initgParty = $doc->createElement('InitgPty');
        // Initiating Party Name
        $initgParty->appendChild(Text::xml($doc, 'Nm', $this->initiatingPartyName));
        if ($this->initiatingPartyNameExt !== null) {
            $ctctDtls = $initgParty->appendChild($doc->createElement('CtctDtls'));
            $ctctDtls->appendChild(Text::xml($doc, 'Nm', $this->initiatingPartyNameExt));

        }
        // Initiating Party Id Details
        $initgParty->appendChild($this->initiatingPartyId->asDom($doc));


        // Initiating Party Contact Details
        $initgParty->appendChild($this->buildContactDetails($doc));

        $header->appendChild($initgParty);
        $root->appendChild($header);

        foreach ($this->payments as $payment) {
            $root->appendChild($payment->asDom($doc));
        }

        // Optional Supplementary Data

        if ($this->supplementaryData) {
            foreach ($this->supplementaryData as $supplement) {
                $root->appendChild($supplement->asDom($doc));
            }
        }

        return $root;
    }
}
