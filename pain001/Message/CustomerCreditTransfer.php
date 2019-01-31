<?php

namespace Consilience\Pain001\Message;

use Consilience\Pain001\Money;
use Consilience\Pain001\Organisation\PartyInterface;
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
     * @var PartyInterface
     */
    protected $initiatingParty;

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
     * @param PartyInterface $initiatingParty Name of the initiating party
     *
     * @throws \InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     */
    public function __construct($id, PartyInterface $initiatingParty)
    {
        $this->id = $id;
        $this->initiatingParty = $initiatingParty;
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
        $header->appendChild($this->initiatingParty->asDom($doc));
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
