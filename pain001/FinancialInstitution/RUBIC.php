<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 19:14
 */

namespace Consilience\Pain001\FinancialInstitution;

use Consilience\Pain001\PostalAddressInterface;
use Consilience\Pain001\Text;
use DOMDocument;
use Consilience\Pain001\AccountInterface;
use Consilience\Pain001\FinancialInstitutionInterface;

/**
 * Class RUBIC
 * Russian Bank Identification Code
 */
class RUBIC implements FinancialInstitutionInterface
{
    protected const LENGTH = 9;
    protected const RU_CLEARING = 'RUCBC';

    /**
     * @var string
     */
    protected $bik;

    /**
     * @var null
     */
    protected $name;

    /**
     * @var PostalAddressInterface
     */
    protected $address;

    /**
     * @param string $bik
     * @throws \InvalidArgumentException
     */
    public function __construct($bik, $name = null, PostalAddressInterface $address = null)
    {
        if (!is_numeric($bik) || strlen($bik) !== self::LENGTH) {
            throw new \InvalidArgumentException('RU bank identification code is not properly formatted.');
        }
        $this->bik = $bik;
        $this->name = Text::assertOptional($name, 70);
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function format()
    {
        return $this->bik;
    }

    /**
     * @return string A ISO 3166-1 alpha-2 country code
     */
    public function getCountry()
    {
        return 'RU';
    }

    /**
     * {@inheritdoc}
     */
    public function asDom(DOMDocument $doc)
    {
        $finInstnId = $doc->createElement('FinInstnId');
        $ClrSysMmbId = $finInstnId->appendChild($doc->createElement('ClrSysMmbId'));
        $ClrSysId = $ClrSysMmbId->appendChild($doc->createElement('ClrSysId'));
        $ClrSysId->appendChild($doc->createElement('Cd', static::RU_CLEARING));
        $ClrSysMmbId->appendChild($doc->createElement('MmbId', $this->format()));

        if ($this->name !== null) {
            $finInstnId->appendChild(Text::xml($doc, 'Nm', $this->name));
        }

        if ($this->address !== null) {
            $finInstnId->appendChild($this->address->asDom($doc));
        }

        return $finInstnId;
    }

    /**
     * Returns a string representation.
     *
     * @return string The string representation.
     */
    public function __toString()
    {
        return $this->format();
    }
}