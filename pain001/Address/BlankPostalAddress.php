<?php

namespace Consilience\Pain001\Address;

use Consilience\Pain001\PostalAddressInterface;

/**
 * An empty postal address, as a placeholder.
 */
class BlankPostalAddress implements PostalAddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function asDom(\DOMDocument $doc)
    {
        return $doc->createElement('PstlAdr');
    }
}
