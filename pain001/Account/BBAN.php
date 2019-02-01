<?php
/**
 * Created by PhpStorm.
 * User: rrr
 * Date: 01.02.2019
 * Time: 20:04
 */

namespace Consilience\Pain001\Account;

use DOMDocument;
use InvalidArgumentException;
use Consilience\Pain001\AccountInterface;
use Consilience\Pain001\Text;

class BBAN implements AccountInterface
{
    protected const SCHME_NM = 'BBAN';

    /**
     * @var string
     */
    protected $id;

    /**
     * Constructor
     *
     * @param string $id
     *
     * @throws InvalidArgumentException When the account identification exceeds the maximum length.
     */
    public function __construct($id)
    {
        $this->id = Text::assert($id, 34);
    }

    /**
     * {@inheritdoc}
     */
    public function format()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function asDom(DOMDocument $doc)
    {
        $root = $doc->createElement('Id');
        $other = $doc->createElement('Othr');
        $other->appendChild(Text::xml($doc, 'Id', $this->format()));
        $schmeNm = $other->appendChild($doc->createElement('SchmeNm'));
        $schmeNm->appendChild(Text::xml($doc, 'Cd', self::SCHME_NM));
        $root->appendChild($other);

        return $root;
    }
}