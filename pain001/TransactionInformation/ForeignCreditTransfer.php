<?php

namespace Consilience\Pain001\TransactionInformation;

use Consilience\Pain001\FinancialInstitution\RUBIC;
use Consilience\Pain001\OrganisationIdentificationInterface;
use DOMDocument;
use Consilience\Pain001\AccountInterface;
use Consilience\Pain001\FinancialInstitution\BIC;
use Consilience\Pain001\FinancialInstitutionAddress;
use Consilience\Pain001\FinancialInstitutionInterface;
use Money\Money;
use Consilience\Pain001\PaymentInformation\PaymentInformation;
use Consilience\Pain001\PostalAddressInterface;

/**
 * ForeignCreditTransfer contains all the information about a foreign (type 6) transaction.
 */
class ForeignCreditTransfer extends CreditTransfer
{
    /**
     * @var AccountInterface
     */
    protected $creditorAccount;

    /**
     * @var BIC|FinancialInstitutionAddress
     */
    protected $creditorAgent;

    /**
     * @var AccountInterface
     */
    protected $creditorAgentAccountDetail;

    /**
     * @var BIC
     */
    protected $intermediaryAgent;

    /**
     * ForeignCreditTransfer constructor.
     * @param $instructionId
     * @param $endToEndId
     * @param Money $amount
     * @param $creditorName
     * @param OrganisationIdentificationInterface $creditorId
     * @param PostalAddressInterface $creditorAddress
     * @param AccountInterface $creditorAccount
     * @param FinancialInstitutionInterface $creditorAgent
     */
    public function __construct(
        $instructionId,
        $endToEndId,
        Money $amount,
        $creditorName,
        PostalAddressInterface $creditorAddress,
        OrganisationIdentificationInterface $creditorId,
        AccountInterface $creditorAccount,
        FinancialInstitutionInterface $creditorAgent,
        AccountInterface $creditorAgentAccountDetail
    ) {
        parent::__construct($instructionId, $endToEndId, $amount, $creditorName, $creditorAddress, $creditorId);

        if (!$creditorAgent instanceof RUBIC && !$creditorAgent instanceof BIC && !$creditorAgent instanceof FinancialInstitutionAddress) {
            throw new \InvalidArgumentException('The creditor agent must be an instance of BIC or FinancialInstitutionAddress.');
        }

        $this->creditorAccount = $creditorAccount;
        $this->creditorAgent = $creditorAgent;
        $this->creditorAgentAccountDetail = $creditorAgentAccountDetail;
    }

    /**
     * Set the intermediary agent of the transaction.
     *
     * @param BIC $intermediaryAgent BIC of the intmediary agent
     */
    public function setIntermediaryAgent(BIC $intermediaryAgent)
    {
        $this->intermediaryAgent = $intermediaryAgent;
    }

    /**
     * {@inheritdoc}
     */
    public function asDom(DOMDocument $doc, PaymentInformation $paymentInformation)
    {
        $root = $this->buildHeader($doc, $paymentInformation);

        if ($this->intermediaryAgent !== null) {
            $intermediaryAgent = $doc->createElement('IntrmyAgt1');
            $intermediaryAgent->appendChild($this->intermediaryAgent->asDom($doc));
            $root->appendChild($intermediaryAgent);
        }

        $creditorAgent = $doc->createElement('CdtrAgt');
        $creditorAgent->appendChild($this->creditorAgent->asDom($doc));
        $root->appendChild($creditorAgent);

        $root->appendChild($this->buildCreditor($doc));

        $creditorAccount = $doc->createElement('CdtrAcct');
        $creditorAccount->appendChild($this->creditorAccount->asDom($doc));
        $root->appendChild($creditorAccount);

        $creditorAccount = $doc->createElement('CdtrAgtAcct');
        $creditorAccount->appendChild($this->creditorAgentAccountDetail->asDom($doc));
        $root->appendChild($creditorAccount);

        $this->appendPurpose($doc, $root);

        $this->appendRemittanceInformation($doc, $root);

        return $root;
    }
}
