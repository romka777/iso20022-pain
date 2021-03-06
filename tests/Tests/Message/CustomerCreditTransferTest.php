<?php

namespace Consilience\Pain001\Tests\Message;

use Consilience\Pain001\FinancialInstitution\BIC;
use Consilience\Pain001\FinancialInstitutionAddress;
use Consilience\Pain001\Account\GeneralAccount;
use Consilience\Pain001\Account\IBAN;
use Consilience\Pain001\FinancialInstitution\IID;
use Consilience\Pain001\Account\ISRParticipant;
use Consilience\Pain001\Message\CustomerCreditTransfer;
use Money\Money;
use Consilience\Pain001\PaymentInformation\CategoryPurposeCode;
use Consilience\Pain001\PaymentInformation\PaymentInformation;
use Consilience\Pain001\PaymentInformation\SEPAPaymentInformation;
use Consilience\Pain001\Account\PostalAccount;
use Consilience\Pain001\Address\StructuredPostalAddress;
use Consilience\Pain001\Tests\TestCase;
use Consilience\Pain001\TransactionInformation\BankCreditTransfer;
use Consilience\Pain001\TransactionInformation\ForeignCreditTransfer;
use Consilience\Pain001\TransactionInformation\IS1CreditTransfer;
use Consilience\Pain001\TransactionInformation\IS2CreditTransfer;
use Consilience\Pain001\TransactionInformation\ISRCreditTransfer;
use Consilience\Pain001\TransactionInformation\PurposeCode;
use Consilience\Pain001\TransactionInformation\SEPACreditTransfer;
use Consilience\Pain001\Address\UnstructuredPostalAddress;

class CustomerCreditTransferTest extends TestCase
{
    const SCHEMA = 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.06';
    //const SCHEMA = 'http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd';
    const SCHEMA_PATH = 'pain.001.001.06.xsd';
    //const SCHEMA_PATH = 'pain.001.001.03.ch.02.xsd';

    /**
     * @return CustomerCreditTransfer
     */
    protected function buildMessage()
    {
        $transaction = new BankCreditTransfer(
            'instr-001',
            'e2e-001',
            Money::CHF(130000), // CHF 1300.00
            'Muster Transport AG',
            new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen', 'GB'),
            new IBAN('CH51 0022 5225 9529 1301 C'),
            new BIC('UBSWCHZH80A')
        );

        $transaction2 = new IS1CreditTransfer(
            'instr-002',
            'e2e-002',
            Money::CHF(30000), // CHF 300.00
            'Finanzverwaltung Stadt Musterhausen',
            new StructuredPostalAddress('Altstadt', '1a', '4998', 'Muserhausen', 'GB'),
            new PostalAccount('80-5928-4')
        );

        $transaction3 = new IS2CreditTransfer(
            'instr-003',
            'e2e-003',
            Money::CHF(20000), // CHF 200.00
            'Druckerei Muster GmbH',
            new StructuredPostalAddress('Gartenstrasse', '61', '3000', 'Bern', 'GB'),
            new IBAN('CH03 0900 0000 3054 1118 8'),
            'Musterbank AG',
            new PostalAccount('80-151-4')
        );

        $iban4 = new IBAN('CH51 0022 5225 9529 1301 C');
        $transaction4 = new BankCreditTransfer(
            'instr-004',
            'e2e-004',
            Money::CHF(30000), // CHF 300.00
            'Muster Transport AG',
            new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen', 'GB'),
            $iban4,
            IID::fromIBAN($iban4)
        );
        $transaction4->setPurpose(new PurposeCode('AIRB'));

        $transaction5 = new SEPACreditTransfer(
            'instr-005',
            'e2e-005',
            Money::EUR(70000), // EUR 700.00
            'Muster Immo AG',
            new UnstructuredPostalAddress('Musterstraße 35', '80333 München', 'DE'),
            new IBAN('DE89 3704 0044 0532 0130 00'),
            new BIC('COBADEFFXXX')
        );

        $transaction6 = new ForeignCreditTransfer(
            'instr-006',
            'e2e-006',
            Money::GBP(6500), // GBP 65.00
            'United Development Ltd',
            new UnstructuredPostalAddress('George Street', 'BA1 2FJ Bath', 'GB'),
            new IBAN('GB29 NWBK 6016 1331 9268 19'),
            new BIC('NWBKGB2L')
        );

        $transaction7 = new ForeignCreditTransfer(
            'instr-007',
            'e2e-007',
            Money::KWD(300001), // KWD 300.001
            'United Development Kuwait',
            new UnstructuredPostalAddress('P.O. Box 23954 Safat', '13100 Kuwait', 'KW'),
            new IBAN('BR97 0036 0305 0000 1000 9795 493P 1'),
            new FinancialInstitutionAddress('Caixa Economica Federal', new UnstructuredPostalAddress('Rua Sao Valentim, 620', '03446-040 Sao Paulo-SP', 'BR'))
        );

        $transaction8 = new ForeignCreditTransfer(
            'instr-008',
            'e2e-008',
            Money::GBP(4500), // GBP 45.00
            'United Development Belgium SA/NV',
            new UnstructuredPostalAddress('Oostjachtpark 187', '6743 Buzenol', 'BE'),
            new GeneralAccount('123-4567890-78'),
            new FinancialInstitutionAddress('Belfius Bank', new UnstructuredPostalAddress('Pachecolaan 44', '1000 Brussel', 'BE'))
        );
        $transaction8->setIntermediaryAgent(new BIC('SWHQBEBB'));

        $transaction9 = new SEPACreditTransfer(
            'instr-009',
            'e2e-009',
            Money::EUR(10000), // EUR 100.00
            'Bau Muster AG',
            new UnstructuredPostalAddress('Musterallee 11', '10115 Berlin', 'DE'),
            new IBAN('DE22 2665 0001 9311 6826 12'),
            new BIC('NOLADE21EMS')
        );

        $transaction10 = new ISRCreditTransfer(
            'instr-010',
            'e2e-010',
            Money::CHF(20000), // CHF 200.00
            new ISRParticipant('01-1439-8'),
            '210000000003139471430009017'
        );

        $transaction11 = new ISRCreditTransfer(
            'instr-011',
            'e2e-011',
            Money::CHF(20000), // CHF 200.00
            new ISRParticipant('01-95106-8'),
            '6019701803969733825'
        );
        $transaction11->setCreditorDetails(
            'Fritz Bischof',
            new StructuredPostalAddress('Dorfstrasse', '17', '9911', 'Musterwald', 'GB')
        );

        $transaction12 = new IS1CreditTransfer(
            'instr-012',
            'e2e-012',
            Money::CHF(50000), // CHF 500.00
            'Meier & Söhne AG',
            new StructuredPostalAddress('Dorfstrasse', '17', '9911', 'Musterwald', 'GB'),
            new PostalAccount('60-9-9')
        );

        $payment = new PaymentInformation('payment-001', 'InnoMuster AG', new BIC('ZKBKCHZZ80A'), new IBAN('CH6600700110000204481'));
        $payment->addTransaction($transaction);
        $payment->addTransaction($transaction2);
        $payment->addTransaction($transaction3);
        $payment->addTransaction($transaction4);

        $payment2 = new PaymentInformation('payment-002', 'InnoMuster AG', new BIC('POFICHBEXXX'), new IBAN('CH6309000000250097798'));
        $payment2->addTransaction($transaction5);
        $payment2->addTransaction($transaction6);
        $payment2->addTransaction($transaction7);
        $payment2->addTransaction($transaction8);

        $payment3 = new SEPAPaymentInformation('payment-003', 'InnoMuster AG', new BIC('POFICHBEXXX'), new IBAN('CH6309000000250097798'));
        $payment3->addTransaction($transaction9);

        $payment4 = new PaymentInformation('payment-004', 'InnoMuster AG', new BIC('POFICHBEXXX'), new IBAN('CH6309000000250097798'));
        $payment4->addTransaction($transaction10);
        $payment4->addTransaction($transaction11);

        $payment5 = new PaymentInformation('payment-005', 'InnoMuster AG', new BIC('POFICHBEXXX'), new IBAN('CH6309000000250097798'));
        $payment5->setCategoryPurpose(new CategoryPurposeCode('SALA'));
        $payment5->addTransaction($transaction12);

        $message = new CustomerCreditTransfer('message-001', 'InnoMuster AG');
        $message->addPayment($payment);
        $message->addPayment($payment2);
        $message->addPayment($payment3);
        $message->addPayment($payment4);
        $message->addPayment($payment5);

        return $message;
    }

    public function testGroupHeader()
    {
        $xml = $this->buildMessage()->asXml();

        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('pain001', self::SCHEMA);

        $nbOfTxs = $xpath->evaluate('string(//pain001:GrpHdr/pain001:NbOfTxs)');
        $this->assertEquals('12', $nbOfTxs);

        $ctrlSum = $xpath->evaluate('string(//pain001:GrpHdr/pain001:CtrlSum)');
        $this->assertEquals('4210.001', $ctrlSum);
    }

    public function testSchemaValidation()
    {
        $xml = $this->buildMessage()->asXml();
        $schemaPath = __DIR__.'/../../'.self::SCHEMA_PATH;

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        libxml_use_internal_errors(true);
        $valid = $doc->schemaValidate($schemaPath);
        foreach (libxml_get_errors() as $error) {
            $this->fail($error->message);
        }
        $this->assertTrue($valid);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
    }

    public function testGetPaymentCount()
    {
        $message = $this->buildMessage();

        $this->assertSame(5, $message->getPaymentCount());
    }
}
