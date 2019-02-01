<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 16:44
 */

namespace Consilience\Pain001;

use DOMDocument;
use DOMElement;

interface OrganisationIdentificationInterface
{
    /**
     * Returns a XML representation to identify the account
     *
     * @param DOMDocument $doc
     *
     * @return DOMElement The built DOM element
     */
    public function asDom(DOMDocument $doc);
}