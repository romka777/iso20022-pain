<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 16:18
 */

require_once __DIR__.'/vendor/autoload.php';

use Money\Money;
use Consilience\Pain001\FinancialInstitution\BIC;
use Consilience\Pain001\Account\IBAN;
use Consilience\Pain001\Message\CustomerCreditTransfer;
use Consilience\Pain001\PaymentInformation\PaymentInformation;
use Consilience\Pain001\Account\PostalAccount;
use Consilience\Pain001\Address\StructuredPostalAddress;
use Consilience\Pain001\TransactionInformation\BankCreditTransfer;
use Consilience\Pain001\TransactionInformation\IS1CreditTransfer;
use Consilience\Pain001\Address\UnstructuredPostalAddress;
use Consilience\Pain001\OrganisationIdentification\Inn;
use Consilience\Pain001\Account\GeneralAccount;
use Consilience\Pain001\FinancialInstitution\RUBIC;
use Consilience\Pain001\Account\BBAN;

$transaction1 = new BankCreditTransfer(
    'instr-001',
    'e2e-001',
    Money::CHF(130000), // CHF 1300.00
    'Muster Transport AG',
    new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen', 'RU'),
    new IBAN('CH51 0022 5225 9529 1301 C'),
    new BIC('UBSWCHZH80A')
);

$transaction2 = new IS1CreditTransfer(
    'instr-002',
    'e2e-002',
    Money::CHF(30000), // CHF 300.00
    'Finanzverwaltung Stadt Musterhausen',
    UnstructuredPostalAddress::sanitize('Altstadt 1a', '4998 Musterhausen'),
    new PostalAccount('80-151-4')
);

$payment = new PaymentInformation(
    'payment-001',
    'InnoMuster AG 1111111111111111111111111111111111111111111111111133111551111111111111111111111111111111111111',
    new Inn('7730189312'),
    new RUBIC('123456789', 'АО "АЛЬФА-БАНК" Г МОСКВА', new UnstructuredPostalAddress(null, null, 'RU')),
    new GeneralAccount('40702810901300013927'),
    new BBAN('40702810901300013927'),
    'RUB',
    new UnstructuredPostalAddress(null, null, 'RU')
);
$payment->setServiceLevel(new \Consilience\Pain001\PaymentInformation\ServiceLevelCode('NURG'));
//$payment->addTransaction($transaction1);
//$payment->addTransaction($transaction2);

$message = new CustomerCreditTransfer(
    'message-001',
    'InnoMuster AG 11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
    new Inn('7730189312')
);
$message->addPayment($payment);

echo $message->asXml(true);