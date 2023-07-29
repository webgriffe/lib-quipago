<?php

namespace spec\Webgriffe\LibQuiPago\Signature;

use PhpSpec\ObjectBehavior;

class InvalidMacExceptionSpec extends ObjectBehavior
{
    function it_is_initializable_and_extends_exception()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Signature\\InvalidMacException');
        $this->shouldHaveType(\Exception::class);
    }
}
