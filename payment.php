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
use Consilience\Pain001\TransactionInformation\ForeignCreditTransfer;
use Consilience\Pain001\PaymentInformation\ServiceLevelCode;
use Consilience\Pain001\TransactionInformation\PurposeProprietary;

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

$transaction = new ForeignCreditTransfer(
    '12323123123',                                                  // Уникальный id платежа
    '20',                                                           // Номер документа
    Money::RUB(5000000),                                            // Сумма в копейках
    'Сахаров Владимир Сергеевич',                                   // ФИО получателя
    new UnstructuredPostalAddress(null, null, 'RU'),                // Адрес получателя
    new Inn('7730189312'),                                          // ИНН получателя
    new BBAN('40702810901300013927'),                               // Счет получателя
    new RUBIC('123456789', 'АО "АЛЬФА-БАНК" Г МОСКВА', new UnstructuredPostalAddress(null, null, 'RU')),    // банк получателя
    new GeneralAccount('40702810901300013927')                      // Корсчет банка получателя
);

$transaction->setServiceLevel(new ServiceLevelCode(ServiceLevelCode::CODE_NURG));   // Срочность
$transaction->setRemittanceInformation('Обычный платеж физ лицу');                  // Комментарий
$transaction->setCreditorReference('123');                                          // Код платежа (УИН)
$transaction->setPurpose(new PurposeProprietary('5'));                              // Очередность платежа

$payment = new PaymentInformation(
    '7730189312_pain_PKG_20180521_00003',                           // Уникальный id пакета платежей
    'ООО "Мир технологий"',                                         // название плательщика
    new Inn('7730189312'),                                          // ИНН плательщика
    new RUBIC('123456789', 'АО "АЛЬФА-БАНК" Г МОСКВА', new UnstructuredPostalAddress(null, null, 'RU')),    // банк плательщика
    new GeneralAccount('40702810901300013927'),                     // Корсчет банка плательщика
    new BBAN('40702810901300013927'),                               // счет плательщика
    'RUB',                                                          // валюта
    new UnstructuredPostalAddress(null, null, 'RU')                 // почтовый адрес плательщика
);
$payment->setServiceLevel(new ServiceLevelCode(ServiceLevelCode::CODE_NURG));   // Срочность
$payment->addTransaction($transaction);
//$payment->addTransaction($transaction1);
//$payment->addTransaction($transaction2);

$message = new CustomerCreditTransfer(
    '7730189312_pain_MSG_20180521_00003',                           // id сообщения
    'ООО "Мир технологий"',                                         // название отправителя сообщения
    new Inn('7730189312')                                           // инн отправителя сообщения
);
$message->addPayment($payment);

echo $message->asXml(true);