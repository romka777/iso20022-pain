<?php
/**
 * Created by PhpStorm.
 * User: Roman Gogolev
 * Date: 31.01.2019
 * Time: 15:23
 */

namespace Consilience\Pain001\Organisation;


use Consilience\Pain001\PostalAddressInterface;
use Consilience\Pain001\Text;

abstract class PartyIdentification implements PartyInterface
{
    protected $name;
    protected $address;
    protected $organisation;

    public function __construct($name, PostalAddressInterface $address = null, OrganisationInterface $organisation = null)
    {
        $this->name = $name;
        $this->address = $address;
        $this->organisation = $organisation;
    }

    abstract function getRootName():string ;

    public function asDom(\DOMDocument $doc)
    {
        $root = $doc->createElement($this->getRootName());
        $root->appendChild(Text::xml($doc, 'Nm', $this->name));
        if ($this->address !== null) {
            $root->appendChild($this->address->asDom($doc));
        }
        if ($this->organisation !== null) {
            $root->appendChild($this->organisation->asDom($doc));
        }

        return $root;
    }

}