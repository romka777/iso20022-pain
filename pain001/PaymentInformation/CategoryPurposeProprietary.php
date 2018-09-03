<?php

namespace Consilience\Pain001\PaymentInformation;

use DOMDocument;
use InvalidArgumentException;
use Consilience\Pain001\Text;

/**
 * CategoryPurposeCode contains a category purpose code from the External Code Sets
 */
class CategoryPurposeProprietary implements CategoryPurposeInterface
{
    /**
     * @var string
     */
    protected $purpose;

    /**
     * Constructor
     *
     * @param string $purpose
     *
     * @throws InvalidArgumentException When the code is not valid
     */
    public function __construct($purpose)
    {
        $purpose = (string) $purpose;

        Text::assert($purpose, 35);

        $this->purpose = $purpose;
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
        return $doc->createElement('Prtry', $this->purpose);
    }
}
