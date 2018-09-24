<?php

namespace Consilience\Pain001\SupplementaryData;

interface ExtensionInterface
{
    /**
     * Generate the extnsion document DOM.
     */
    public function asDom(\DOMDocument $doc) : \DOMElement;
}
