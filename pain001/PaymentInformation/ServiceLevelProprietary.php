<?php

namespace Consilience\Pain001\PaymentInformation;

use DOMDocument;
use InvalidArgumentException;
use Consilience\Pain001\Text;

/**
 * ServiceLevelProprietary
 */
class ServiceLevelProprietary implements ServiceLevelInterface
{
    /**
     * @var string
     */
    protected $level;

    /**
     * Constructor
     *
     * @param string $purpose
     *
     * @throws InvalidArgumentException When the code is not valid
     */
    public function __construct($level)
    {
        $level = (string) $level;

        Text::assert($level, 35);

        $this->level = $level;
    }

    /**
     * Returns a XML representation of this level
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement The built DOM element
     */
    public function asDom(DOMDocument $doc)
    {
        return $doc->createElement('Prtry', $this->level);
    }
}
