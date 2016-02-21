<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Webgriffe_LibQuiPago_Notification_InvalidMacExceptionSpec extends ObjectBehavior
{
    function it_is_initializable_and_extends_exception()
    {
        $this->shouldHaveType('Webgriffe_LibQuiPago_Notification_InvalidMacException');
        $this->shouldHaveType(\Exception::class);
    }
}
