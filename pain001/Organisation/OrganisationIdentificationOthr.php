<?php
/**
 * Created by PhpStorm.
 * User: Roman Gogolev
 * Date: 31.01.2019
 * Time: 15:26
 */

namespace Consilience\Pain001\Organisation;


use Consilience\Pain001\Text;

class OrganisationIdentificationOthr implements OrganisationInterface
{
    protected $id;
    protected $schmeNm;
    protected $issr;

    public function __construct($id, $schmeNm = null, $issr = null)
    {
        $this->id = $id;
        $this->schmeNm = $schmeNm;
        $this->issr = $issr;

    }

    public function asDom(\DOMDocument $doc)
    {
        $xml = $doc->createElement('OrgId');
        $othr = $xml->appendChild($doc->createElement('Othr'));
        $othr->appendChild(Text::xml($doc, 'Id', $this->id));
        if ($this->schmeNm !== null) {
            $schmeNm = $othr->appendChild($doc->createElement('SchmeNm'));
            $schmeNm->appendChild(Text::xml($doc, 'Cd', $this->schmeNm));
        }

        if ($this->issr !== null) {
            $othr->appendChild(Text::xml($doc, 'Issr', $this->issr));
        }

        return $xml;
    }

}