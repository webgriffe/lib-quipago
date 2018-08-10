<?php

namespace spec\Webgriffe\LibQuiPago\Signature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InvalidMacExceptionSpec extends ObjectBehavior
{
    function it_is_initializable_and_extends_exception()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Signature\\InvalidMacException');
        $this->shouldHaveType(\Exception::class);
    }
}
