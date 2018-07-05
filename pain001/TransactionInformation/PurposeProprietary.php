<?php

namespace Consilience\Pain001\TransactionInformation;

use DOMDocument;
use InvalidArgumentException;

/**
 * PurposeProprietary contains a purpose text.
 * Used for domestic transfers, this is what would appear on a
 * creditor's bank statement.
 * Different countries have different validation rules, and many
 * are much shorter than the maximum 35 characters for this element.
 */
class PurposeProprietary implements PurposeInterface
{
    /**
     * @var string the reference for the creditors account (domestic only, in most countries)
     */
    protected $proprietary;

    /**
     * Constructor
     *
     * @param string $proprietary
     *
     * @throws \InvalidArgumentException When the proprietary is not valid
     */
    public function __construct($proprietary)
    {
        $proprietary = (string) $proprietary;

        if (! preg_match('/^.{1,35}$/', $proprietary)) {
            throw new InvalidArgumentException('The purpose proprietary is not valid.');
        }

        $this->proprietary = $proprietary;
    }

    /**
     * Returns a XML representation of this purpose
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement The built DOM element
     */
    public function asDom(DOMDocument $doc)
    {
        return $doc->createElement('Prtry', $this->proprietary);
    }
}
