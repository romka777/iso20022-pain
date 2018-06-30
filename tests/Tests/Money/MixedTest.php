<?php

namespace Consilience\Pain001\Tests\Money;

use Consilience\Pain001\Money\Mixed;
use Money\Money;
use Consilience\Pain001\Tests\TestCase;

class MixedTest extends TestCase
{
    /**
     * @covers \Consilience\Pain001\Money\Mixed::plus
     */
    public function testPlus()
    {
        $sum = new Mixed(0);
        $sum = $sum->add(Money::CHF(2456));
        $sum = $sum->add(Money::CHF(1000));
        $sum = $sum->add(Money::JPY(1200));

        $this->assertEquals('1234.56', $sum->format());
    }

    /**
     * @covers \Consilience\Pain001\Money\Mixed::minus
     */
    public function testMinus()
    {
        $sum = new Mixed(100);
        $sum = $sum->subtract(Money::CHF(5000));
        $sum = $sum->subtract(Money::CHF(99));
        $sum = $sum->subtract(Money::JPY(300));

        $this->assertEquals('-250.99', $sum->format());
    }
}
