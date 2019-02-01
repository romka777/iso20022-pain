<?php

namespace Consilience\Pain001\PaymentInformation;

use Consilience\Pain001\Account\GeneralAccount;
use Consilience\Pain001\FinancialInstitution\BIC;
use Consilience\Pain001\FinancialInstitutionInterface;
use Consilience\Pain001\OrganisationIdentificationInterface;
use Consilience\Pain001\PostalAddressInterface;
use Consilience\Pain001\AccountInterface;
use Consilience\Pain001\Account\IBAN;
use Consilience\Pain001\Account\GBBankAccount;
use Consilience\Pain001\FinancialInstitution\GBDSC;
use Consilience\Pain001\Money\Mixed;
use Consilience\Pain001\Text;
use Consilience\Pain001\TransactionInformation\CreditTransfer;
use Money\Money;

/**
 * PaymentInformation contains a group of transactions as well as details about the debtor
 */
class PaymentInformation
{
    protected const PRIORITY_NORM = 'NORM';
    protected const PRIORITY_HIGH = 'HIGH';

    /**
     * @var string Payment Method (TransferAdvice)
     */
    const PAYMENT_METHOD_TRANSFERADVICE = 'TRF';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $transactions;

    /**
     * @var bool
     */
    protected $batchBooking;

    /**
     * @var string|null
     */
    protected $serviceLevel;

    /**
     * @var string|null
     */
    protected $localInstrument;

    /**
     * @var CategoryPurposeCode|null
     */
    protected $categoryPurpose;

    /**
     * @var \DateTime
     */
    protected $executionDate;

    /**
     * @var string
     */
    protected $debtorName;

    /**
     * @var string
     */
    protected $debtorNameExt;

    /**
     * @var OrganisationIdentificationInterface
     */
    protected $debtorid;

    /**
     * @var FinancialInstitutionInterface
     */
    protected $debtorAgent;

    /**
     * @var AccountInterface
     */
    protected $debtorAgentAccountDetail;

    /**
     * @var AccountInterface
     */
    protected $debtorAccountDetail;

    /**
     * @var string
     */
    protected $debtorAccountCurreny;

    /**
     * @var PostalAddressInterface
     */
    protected $debtorPostalAddress;

    /**
     * @var string
     */
    protected $priority = self::PRIORITY_NORM;

    /**
     * Constructor
     *
     * @param string  $id          Identifier of this group (should be unique within a message)
     * @param string  $debtorName  Name of the debtor
     * @param OrganisationIdentificationInterface  $debtorId  Id of the debtor
     * @param FinancialInstitutionInterface $debtorAgent BIC or IID of the debtor's financial institution
     * @param AccountInterface $debtorAgentAccountDetail
     * @param AccountInterface $debtorAccountDetail
     * @param string $debtorAccountCurrency  Debtor's account currency
     * @param PostalAddressInterface $debtorPostalAddress  debtor's postal address
     *
     * @throws \InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     * @throws \Exception
     */
    public function __construct(
        $id,
        $debtorName,
        OrganisationIdentificationInterface $debtorId,
        FinancialInstitutionInterface $debtorAgent,
        AccountInterface $debtorAgentAccountDetail,
        AccountInterface $debtorAccountDetail,
        $debtorAccountCurrency = null,
        PostalAddressInterface $debtorPostalAddress = null
    ) {
        $this->id = Text::assertIdentifier($id);
        $this->transactions = [];
        $this->batchBooking = true;

        // FIXME: This will pick up the local timezone by default.
        // We want to make sure we always get the right day when hovering
        // around midnight.
        // For now, it is best to always set this date explicitly.

        $this->executionDate = new \DateTime();

        if (mb_strlen($debtorName, 'UTF-8') <= 70) {
            $this->debtorName = Text::assert($debtorName, 70);
        } else {
            $this->debtorName = Text::assertPattern(mb_substr($debtorName, 0, 70, 'UTF-8'));
            $this->debtorNameExt = Text::assertPattern(mb_substr($debtorName, 70, null, 'UTF-8'));
        }

        $this->debtorid = $debtorId;
        $this->debtorAgent = $debtorAgent;
        $this->debtorAgentAccountDetail = $debtorAgentAccountDetail;
        $this->debtorAccountDetail = $debtorAccountDetail;
        $this->debtorAccountCurreny = Text::assertOptional($debtorAccountCurrency, 3);
        $this->debtorPostalAddress = $debtorPostalAddress;
    }

    /**
     * Adds a single transaction to this payment
     *
     * @param CreditTransfer $transaction The transaction to be added
     *
     * @return PaymentInformation This payment instruction
     */
    public function addTransaction(CreditTransfer $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    /**
     * Gets the number of transactions
     *
     * @return int Number of transactions
     */
    public function getTransactionCount()
    {
        return count($this->transactions);
    }

    /**
     * Gets the sum of transactions
     *
     * @return Mixed Sum of transactions
     */
    public function getTransactionSum()
    {
        $sum = new Mixed(0);

        foreach ($this->transactions as $transaction) {
            $sum = $sum->add($transaction->getAmount());
        }

        return $sum;
    }

    /**
     * Sets the required execution date.
     * Where appropriate, the value data is automatically modified to the next possible banking/Post Office working day.
     *
     * @param \DateTime $executionDate
     *
     * @return PaymentInformation This payment instruction
     */
    public function setExecutionDate(\DateTime $executionDate)
    {
        $this->executionDate = $executionDate;

        return $this;
    }

    /**
     * Sets the batch booking option.
     * It is recommended that one payment instruction is created for each currency transferred.
     *
     * false = Single booking requested
     * true = Batch booking requested
     *
     * @param bool $batchBooking
     *
     * @return PaymentInformation This payment instruction
     */
    public function setBatchBooking($batchBooking)
    {
        $this->batchBooking = boolval($batchBooking);

        return $this;
    }

    /**
     * Checks whether the payment type information is included on B- or C-level
     *
     * @return bool true if it is included on B-level
     */
    public function hasPaymentTypeInformation()
    {
        return (
            $this->localInstrument !== null
            || $this->serviceLevel !== null
            || $this->categoryPurpose !== null
            || $this->priority !== null
        );
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
     * Sets the local instrument.
     * Payment Type: 10=SIP 30=SOP 40=FDP
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

    public function setServiceLevel(ServiceLevelInterface $value)
    {
        $this->serviceLevel = $value;
        return $this;
    }

    /**
     * Sets the category purpose
     *
     * @param CategoryPurposeInterface $categoryPurpose The category purpose
     *
     * @return PaymentInformation This payment instruction
     */
    public function setCategoryPurpose(CategoryPurposeInterface $categoryPurpose)
    {
        $this->categoryPurpose = $categoryPurpose;
        return $this;
    }

    /**
     * @return $this
     */
    public function setNormPriority()
    {
        $this->priority = self::PRIORITY_NORM;
        return $this;
    }

    /**
     * @return $this
     */
    public function setHighPriority()
    {
        $this->priority = self::PRIORITY_HIGH;
        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Builds a DOM tree of this payment instruction
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement The built DOM tree
     */
    public function asDom(\DOMDocument $doc)
    {
        $root = $doc->createElement('PmtInf');

        $root->appendChild(Text::xml($doc, 'PmtInfId', $this->id));

        // Payment Method.
        // CHK (Cheque), TRF (TransferAdvice), TRA (CreditTransfer)
        // Must always be TRF for the electronic payments we are dealing with here..

        $root->appendChild($doc->createElement(
            'PmtMtd',
            static::PAYMENT_METHOD_TRANSFERADVICE
        ));

        $root->appendChild($doc->createElement(
            'BtchBookg',
            ($this->batchBooking ? 'true' : 'false')
        ));

        // Payment Type: 10=SIP 30=SOP 40=FDP

        if ($this->hasPaymentTypeInformation()) {
            $paymentType = $doc->createElement('PmtTpInf');
            $localInstrument = $this->localInstrument ?: $this->inferLocalInstrument();

            if ($localInstrument !== null) {
                $localInstrumentNode = $doc->createElement('LclInstrm');
                $localInstrumentNode->appendChild($doc->createElement('Prtry', $localInstrument));
                $paymentType->appendChild($localInstrumentNode);
            }

            $serviceLevel = $this->serviceLevel ?: $this->inferServiceLevel();

            if ($serviceLevel !== null) {
                $serviceLevelNode = $doc->createElement('SvcLvl');
                //$serviceLevelNode->appendChild($doc->createElement('Cd', $serviceLevel));
                $serviceLevelNode->appendChild($serviceLevel->asDom($doc));
                $paymentType->appendChild($serviceLevelNode);
            }

            if ($this->categoryPurpose !== null) {
                $categoryPurposeNode = $doc->createElement('CtgyPurp');
                $categoryPurposeNode->appendChild($this->categoryPurpose->asDom($doc));
                $paymentType->appendChild($categoryPurposeNode);
            }

            if ($this->priority !== null) {
                $priority = $doc->createElement('InstrPrty', $this->priority);
                $paymentType->appendChild($priority);
            }


                $root->appendChild($paymentType);
        }

        $root->appendChild(
            $doc->createElement(
                'ReqdExctnDt',
                $this->executionDate->format('Y-m-d')
            )
        );

        // Debtor name and postal address.
        $debtor = $doc->createElement('Dbtr');

        $debtor->appendChild(Text::xml($doc, 'Nm', $this->debtorName));

        if ($this->debtorNameExt !== null) {
            $ctctDtls = $debtor->appendChild($doc->createElement('CtctDtls'));
            $ctctDtls->appendChild(Text::xml($doc, 'Nm', $this->debtorNameExt));
        }

        // Include the optional debtor postal address if supplied.
        if ($this->debtorPostalAddress !== null) {
            $debtor->appendChild($this->debtorPostalAddress->asDom($doc));
        }

        // Debtor Id Details
        $debtor->appendChild($this->debtorid->asDom($doc));

        $root->appendChild($debtor);

        // Debtor account
        $debtorAccount = $doc->createElement('DbtrAcct');
        $debtorAccountId = $doc->createElement('Id');

        if ($this->debtorAccountDetail instanceof IBAN) {
            $debtorAccountId->appendChild(
                $doc->createElement('IBAN', $this->debtorAccountDetail->normalize())
            );
        } elseif ($this->debtorAccountDetail instanceof GBBankAccount) {
            $other = $debtorAccountId->appendChild(
                $doc->createElement('Othr')
            );

            $other->appendChild(
                $doc->createElement('Id', $this->debtorAccountDetail->normalize())
            );
        } else {
            $debtorAccountId = $this->debtorAccountDetail->asDom($doc);
        }

        $debtorAccount->appendChild($debtorAccountId);

        if ($this->debtorAccountCurreny !== null) {
            $debtorAccount->appendChild(Text::xml($doc, 'Ccy', $this->debtorAccountCurreny));
        }

        $root->appendChild($debtorAccount);

        // Debtor agent
        $debtorAgent = $doc->createElement('DbtrAgt');
        $debtorAgent->appendChild($this->debtorAgent->asDom($doc));
        $root->appendChild($debtorAgent);

        // Debtor agent account
        $debtorAgentAccount = $doc->createElement('DbtrAgtAcct');
        $debtorAgentAccount->appendChild($this->debtorAgentAccountDetail->asDom($doc));
        $root->appendChild($debtorAgentAccount);


        // FIXME: the $localInstrument here will not even be set if
        // there is no payment information set further up.

        foreach ($this->transactions as $transaction) {
            if ($this->hasPaymentTypeInformation()) {
                if (! empty($transaction->getLocalInstrument()) && $transaction->getLocalInstrument() !== $localInstrument) {
                    throw new \LogicException(sprintf(
                        'You can not set the local instrument (%s) on B- and C-level; conflicts with (%s).',
                        $localInstrument,
                        $transaction->getLocalInstrument()
                    ));
                }

                $transactionServiceLevel = $transaction->getServiceLevel();

                if ($transactionServiceLevel !== null && $transactionServiceLevel !== $serviceLevel) {
                    throw new \LogicException('You can not set the service level on B- and C-level.');
                }
            }
            $root->appendChild($transaction->asDom($doc, $this));
        }

        return $root;
    }

    private function inferServiceLevel()
    {
        if (! count($this->transactions)) {
            return null;
        }

        return $this->transactions[0]->getServiceLevel();
    }

    private function inferLocalInstrument()
    {
        if (! count($this->transactions)) {
            return null;
        }

        return $this->transactions[0]->getLocalInstrument();
    }
}
