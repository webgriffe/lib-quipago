<?php

namespace spec\Webgriffe\LibQuiPago\Signature;

use PhpSpec\ObjectBehavior;
use Webgriffe\LibQuiPago\Signature\InvalidMacException;

class InvalidMacExceptionSpec extends ObjectBehavior
{
    public function it_is_initializable_and_extends_exception(): void
    {
        $this->shouldHaveType(InvalidMacException::class);
        $this->shouldHaveType(\Exception::class);
    }
}
