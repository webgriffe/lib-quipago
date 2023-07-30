<?php

namespace spec\Webgriffe\LibQuiPago\Signature;

use PhpSpec\ObjectBehavior;

class InvalidMacExceptionSpec extends ObjectBehavior
{
    public function it_is_initializable_and_extends_exception(): void
    {
        $this->shouldHaveType(\Webgriffe\LibQuiPago\Signature\InvalidMacException::class);
        $this->shouldHaveType(\Exception::class);
    }
}
