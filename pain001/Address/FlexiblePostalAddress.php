<?php

namespace Consilience\Pain001\Address;

/**
 * Implements a PostalAddress6.
 */

use Consilience\Pain001\PostalAddressInterface;
use Consilience\Pain001\Text;

class FlexiblePostalAddress implements PostalAddressInterface
{
    /**
     * @var array supplied parts of the address.
     */
    protected $parts;

    /**
     * The mappings of long names to XML element names.
     * Note there is no validation on the parts at this stage,
     * so be careful when setting them. Source:
     *
     * <xs:element maxOccurs="1" minOccurs="0" name="AdrTp" type="AddressType2Code"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="Dept" type="Max70Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="SubDept" type="Max70Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="StrtNm" type="Max70Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="BldgNb" type="Max16Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="PstCd" type="Max16Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="TwnNm" type="Max35Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="CtrySubDvsn" type="Max35Text"/>
     * <xs:element maxOccurs="1" minOccurs="0" name="Ctry" type="CountryCode"/>
     * <xs:element maxOccurs="7" minOccurs="0" name="AdrLine" type="Max70Text"/>
     *
     * AddressType2Code is one of:
     * ADDR PBOX HOME BIZZ MLTO DLVY
     *
     * CountryCode is two-character ISO.
     */
    protected $mapping = [
        'AddressType'           => 'AdrTp',
        'Department'            => 'Dept',
        'SubDepartment'         => 'SubDept',
        'StreetName'            => 'StrtNm',
        'BuildingNumber'        => 'BldgNb',
        'postCode'              => 'PstCd',
        'TownName'              => 'TwnNm',
        'CountrySubDivision'    => 'CtrySubDvsn',
        'Country'               => 'Ctry',
        'AddressLine'           => 'AdrLine',
    ];

    /**
     * The "AddressLine" element can be a string for a single line or an array
     * for multiple lines.
     * 
     * @param array $parts Array of address parts indexed by mapping names
     */
    public function __construct(array $parts = [])
    {
        $this->parts = $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function asDom(\DOMDocument $doc)
    {
        $root = $doc->createElement('PstlAdr');

        foreach ($this->mapping as $itemName => $xmlName) {
            // Allow parts to be indexed as upper or lower camel case.
            $name = ucfirst($itemName);

            if (array_key_exists($name, $this->parts)) {
                $value = $this->parts[$name];

                if ($value === null) {
                    continue;
                }

                if ($name === 'AddressLine') {
                    // Can have multiple address lines.

                    if (is_string($value)) {
                        $value = [$value];
                    }

                    foreach ($value as $line) {
                        $root->appendChild(Text::xml($doc, $xmlName, $line));
                    }
                } else {
                    // Single occurance string.
                    $root->appendChild(Text::xml($doc, $xmlName, $value));
                }
            }
        }

        return $root;
    }
}
