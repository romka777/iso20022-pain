<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 16:45
 */

namespace Consilience\Pain001\OrganisationIdentification;


use Consilience\Pain001\OrganisationIdentificationInterface;
use Consilience\Pain001\Text;
use DOMDocument;

class Inn implements OrganisationIdentificationInterface
{
    protected const SCHME_NM = 'TXID';
    protected $inn;

    public function __construct($inn)
    {
        $this->inn = Text::assertLengths($inn, [10, 12]);
    }

    public function asDom(DOMDocument $doc)
    {
        $root = $doc->createElement('Id');
        $other = $root->appendChild($doc->createElement('Othr'));
        $other->appendChild($doc->createElement('Id', $this->inn));
        $schmeNm = $other->appendChild($doc->createElement('SchmeNm'));
        $schmeNm->appendChild($doc->createElement('Cd', self::SCHME_NM));

        return $root;
    }

}