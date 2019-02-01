<?php
/**
 * Created by PhpStorm.
 * User: Roman Gogolev
 * Date: 31.01.2019
 * Time: 15:08
 */


require_once 'vendor/autoload.php';

use Consilience\Pain001\FinancialInstitution\BIC;
use Consilience\Pain001\Account\IBAN;
use Consilience\Pain001\Message\CustomerCreditTransfer;
use Money\Money;
use Consilience\Pain001\PaymentInformation\PaymentInformation;
use Consilience\Pain001\Account\PostalAccount;
use Consilience\Pain001\Address\StructuredPostalAddress;
use Consilience\Pain001\TransactionInformation\BankCreditTransfer;
use Consilience\Pain001\TransactionInformation\IS1CreditTransfer;
use Consilience\Pain001\Address\UnstructuredPostalAddress;
use Consilience\Pain001\Account\GeneralAccount;
use Consilience\Pain001\Organisation\InitPartyIdentification;
use Consilience\Pain001\Organisation\OrganisationIdentificationOthr;
use Consilience\Pain001\Organisation\DebitorIdentification;
use Consilience\Pain001\Account\DebitorAccount;

//$transaction1 = new BankCreditTransfer(
//    '7730189312_pain_PKG_20180521_00003',   // Уникальный id ВП CdtTrfTxInf.PmtId.InstrId
//    'e2e-001',                              // Номер документа (3) CdtTrfTxInf.PmtId.EndToEndId
//    Money::RUB(50000.00),                   // Сумма документа CdtTrfTxInf.Amt.InstdAmt
//    'Muster Transport AG',                  // Наименование плательщика (8) Dbtr.Nm
//    new UnstructuredPostalAddress(null, null, 'RU'),
//    new GeneralAccount('CH51 0022 5225 9529 1301 C'),
//    new BIC('UBSWCHZH80A')
//);


$payment = new PaymentInformation(
    '7730189312_pain_PKG_20180521_00003',
    new DebitorIdentification('ООО "Мир технологий"', null, new OrganisationIdentificationOthr('7730189312', 'TXID')),
    new BIC('ZKBKCHZZ80A'),
    new DebitorAccount('RUB', new OrganisationIdentificationOthr('7730189312', 'TXID'))
);
//$payment->addTransaction($transaction1);

$message = new CustomerCreditTransfer(
    '7730189312_pain_MSG_20180521_00003',          // Уникальный id сообщения с ВП
    new InitPartyIdentification('ООО "Мир технологий"', null, new OrganisationIdentificationOthr('7730189312', 'TXID'))
);
$message->addPayment($payment);

echo $message->asXml(true);