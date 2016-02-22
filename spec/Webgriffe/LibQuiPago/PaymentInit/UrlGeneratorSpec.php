<?php

namespace spec\Webgriffe\LibQuiPago\PaymentInit;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UrlGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable_and_generates_correct_url()
    {
        $this->beConstructedWith(
            'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet',
            'merchant_alias',
            50.50,
            'EUR',
            '1200123',
            'http-cancel-url',
            'secret_key',
            'customer@mail.com',
            'http-succes-url',
            'SESSID123',
            'ITA',
            'http-post-url'
        );
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\UrlGenerator');
        $this->generate()->shouldReturn(
            'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet' .
            '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url_back=http-cancel-url' .
            '&mail=customer%40mail.com&url=http-succes-url&session_id=SESSID123' .
            '&languageId=ITA&urlpost=http-post-url&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf'
        );
    }
}
