<?php
/**
 * Created by PhpStorm.
 * User: Roman Gogolev
 * Date: 31.01.2019
 * Time: 15:45
 */

namespace Consilience\Pain001\Organisation;


class DebitorIdentification extends PartyIdentification
{
    public function getRootName(): string
    {
        return 'Dbtr';
    }

}