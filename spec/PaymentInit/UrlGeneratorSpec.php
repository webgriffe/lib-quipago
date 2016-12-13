<?php

namespace spec\Webgriffe\LibQuiPago\PaymentInit;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class UrlGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable_and_generates_correct_url()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\UrlGenerator');
        $this
            ->generate(
                'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'sha1',
                50.50,
                'EUR',
                '1200123',
                'http-cancel-url',
                'customer@mail.com',
                'http-succes-url',
                'SESSID123',
                'ITA',
                'http-post-url'
            )
            ->shouldReturn(
                'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet' .
                '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url_back=http-cancel-url' .
                '&mail=customer%40mail.com&url=http-succes-url&session_id=SESSID123' .
                '&languageId=ITA&urlpost=http-post-url&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf'
            )
        ;
    }

    function it_should_log_url_generation_process_if_a_logger_is_given(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
        $logger->debug('Webgriffe\\LibQuiPago\\PaymentInit\\UrlGenerator::generate method called')->shouldBeCalled();
        $logger->debug('MAC calculation string is "codTrans=1200123divisa=EURimporto=5050secret_key"')->shouldBeCalled();
        $logger->debug('MAC calculation method is "sha1"')->shouldBeCalled();
        $logger->debug('Calculated MAC is "0fa0ca05a13c6b5d0bd1466461319658f7f990bf"')->shouldBeCalled();
        $expectedUrl = 'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet' .
            '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url_back=http-cancel-url' .
            '&mail=customer%40mail.com&url=http-succes-url&session_id=SESSID123' .
            '&languageId=ITA&urlpost=http-post-url&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf';
        $logger->debug('Generated URL is "' . $expectedUrl . '"')->shouldBeCalled();
        $this
            ->generate(
                'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'sha1',
                50.50,
                'EUR',
                '1200123',
                'http-cancel-url',
                'customer@mail.com',
                'http-succes-url',
                'SESSID123',
                'ITA',
                'http-post-url'
            )
            ->shouldReturn($expectedUrl)
        ;
    }

    function it_should_use_md5_method_if_specified()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\UrlGenerator');
        $this
            ->generate(
                'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'md5',
                50.50,
                'EUR',
                '1200123',
                'http-cancel-url',
                'customer@mail.com',
                'http-succes-url',
                'SESSID123',
                'ITA',
                'http-post-url'
            )
            ->shouldReturn(
                'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet' .
                '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url_back=http-cancel-url' .
                '&mail=customer%40mail.com&url=http-succes-url&session_id=SESSID123' .
                '&languageId=ITA&urlpost=http-post-url&mac=ZjkyM2NhY2I0M2YyYTA4Y2ViMTEwZDFjZTY5MjE5Zjk%253D'
            )
        ;
    }

    function it_should_throw_an_invalid_argument_exception_if_mac_method_is_not_sha1_or_md5()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'generate',
                array(
                    'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet',
                    'merchant_alias',
                    'secret_key',
                    'invalid', // <- Invalid MAC method
                    50.50,
                    'EUR',
                    '1200123',
                    'http-cancel-url',
                    'customer@mail.com',
                    'http-succes-url',
                    'SESSID123',
                    'ITA',
                    'http-post-url'
                )
            )
        ;
    }

    function it_should_throw_an_invalid_argument_exception_if_currency_is_not_allowed()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'generate',
                array(
                    'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet',
                    'merchant_alias',
                    'secret_key',
                    'sha1',
                    50.50,
                    'XXX', // <- Invalid currency
                    '1200123',
                    'http-cancel-url',
                    'customer@mail.com',
                    'http-succes-url',
                    'SESSID123',
                    'ITA',
                    'http-post-url'
                )
            )
        ;
    }
}
