<?php
/**
 * Created by PhpStorm.
 * User: Roman Gogolev
 * Date: 31.01.2019
 * Time: 15:22
 */

namespace Consilience\Pain001\Organisation;


interface OrganisationInterface
{
    /**
     * Returns a XML representation to identify the financial institution
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement The built DOM element
     */
    public function asDom(\DOMDocument $doc);
}

