<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 17:35
 */

namespace Consilience\Pain001\PaymentInformation;

use DOMDocument;
use InvalidArgumentException;
use Consilience\Pain001\Text;

class ServiceLevelCode implements ServiceLevelInterface
{
    public const CODE_NURG = 'NURG';    // обычный
    public const CODE_URGP = 'URGP';    // срочный

    protected $code;

    /**
     * @param string $code
     * @throws InvalidArgumentException When the code is not valid
     */
    public function __construct($code)
    {
        $this->code = Text::assert($code, 4);
    }

    /**
     * @param \DOMDocument $doc
     * @return \DOMElement The built DOM element
     */
    public function asDom(DOMDocument $doc)
    {
        return $doc->createElement('Cd', $this->code);
    }

    public function getCode()
    {
        return $this->code;
    }
}