<?php

namespace spec\Webgriffe\LibQuiPago\Notification;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webgriffe\LibQuiPago\Notification\InvalidMacException;

class HandlerSpec extends ObjectBehavior
{
    function it_throws_an_exception_if_some_parameter_is_missing()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        unset($requestRawParams['alias']);
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', array('secret_key', $requestRawParams));
    }

    function it_throws_an_exception_if_mac_is_wrong()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'invalid-mac';
        $this->shouldThrow(InvalidMacException::class)->during('handle', array('secret_key', $requestRawParams));
    }

    function it_returns_mapped_params()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $this->handle('secret_key', $this->getRequestRawParams());

        $this->getTransactionCode()->shouldReturn('1200123');
        $this->isTransactionResultPositive()->shouldReturn(true);
        $this->getAmount()->shouldReturn(50.50);
        $this->getCurrency()->shouldReturn('EUR');
        $this->getTransactionDate()->shouldHaveType(\DateTime::class);
        $this->getTransactionDate()->format('d/m/Y H:i:s')->shouldReturn('21/02/2016 18:18:54');
        $this->getAuthCode()->shouldReturn('123abc');
        $this->getMacFromRequest()->shouldReturn('04fbcde788ac39d9760fa23802dbf7cfda5ced69');
        $this->getMerchantAlias()->shouldReturn('merchant_123');
        $this->getSessionId()->shouldReturn('123123');
        $this->getCardBrand()->shouldReturn('Visa');
        $this->getFirstName()->shouldReturn('John');
        $this->getLastName()->shouldReturn('Doe');
        $this->getEmail()->shouldReturn('jd@mail.com');
    }

    function it_returns_negative_result_if_transaction_result_is_ko()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['esito'] = 'KO';
        $requestRawParams['mac'] = 'ed80e2807c8eb110fcf90a50b8c99a2f3bb21f95';
        $this->handle('secret_key', $requestRawParams);
        $this->isTransactionResultPositive()->shouldReturn(false);
    }

    /**
     * @return array
     */
    private function getRequestRawParams()
    {
        return array(
            'codTrans' => '1200123',
            'esito' => 'OK',
            'importo' => 50.50,
            'divisa' => 'EUR',
            'data' => '20160221',
            'orario' => '181854',
            'codAut' => '123abc',
            'mac' => '04fbcde788ac39d9760fa23802dbf7cfda5ced69',
            'alias' => 'merchant_123',
            'session_id' => '123123',
            '$BRAND' => 'Visa',
            'nome' => 'John',
            'cognome' => 'Doe',
            'email' => 'jd@mail.com',
        );
    }
}
