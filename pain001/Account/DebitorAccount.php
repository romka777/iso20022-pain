<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 16:05
 */

namespace Consilience\Pain001\Account;


use Consilience\Pain001\AccountInterface;
use Consilience\Pain001\Organisation\OrganisationIdentificationOthr;
use Consilience\Pain001\Text;
use DOMDocument;

class DebitorAccount  implements AccountInterface
{
    protected $currency;
    protected $org;


    public function __construct($currency, OrganisationIdentificationOthr $org)
    {
        $this->currency = $currency;
        $this->org = $org;

    }

    public function asDom(DOMDocument $doc)
    {
        $acc = $doc->createElement('DbtrAcct');
        $acc->appendChild($this->org->asDom($doc));
        $acc->appendChild(Text::xml($doc, 'Ccy', $this->currency));
    }

    public function format()
    {
        // TODO: Implement format() method.
    }

}