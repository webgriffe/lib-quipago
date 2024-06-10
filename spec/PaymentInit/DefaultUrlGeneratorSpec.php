<?php

namespace spec\Webgriffe\LibQuiPago\PaymentInit;

use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class DefaultUrlGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable_and_generates_correct_url(): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\DefaultUrlGenerator');
        $this
            ->generate(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'sha1',
                50.50,
                '1200123',
                'http-cancel-url',
                'customer@mail.com',
                'http-succes-url',
                'SESSID123',
                'ITA',
                'http-post-url'
            )
            ->shouldReturn(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet'.
                '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url=http-succes-url'.
                '&url_back=http-cancel-url&urlpost=http-post-url&mail=customer%40mail.com'.
                '&languageId=ITA&session_id=SESSID123&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf'
            )
        ;
    }

    public function it_should_log_url_generation_process_if_a_logger_is_given(LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
        $logger->debug('Webgriffe\\LibQuiPago\\PaymentInit\\DefaultUrlGenerator::generate method called')->shouldBeCalled();
        $logger->debug('MAC calculation string is "codTrans=1200123divisa=EURimporto=5050secret_key"')->shouldBeCalled();
        $logger->debug('MAC calculation method is "sha1"')->shouldBeCalled();
        $logger->debug('Calculated MAC is "0fa0ca05a13c6b5d0bd1466461319658f7f990bf"')->shouldBeCalled();

        $log = <<<STR
Request params: Array
(
    [alias] => merchant_alias
    [importo] => 5050
    [divisa] => EUR
    [codTrans] => 1200123
    [url] => http-succes-url
    [url_back] => http-cancel-url
    [urlpost] => http-post-url
    [mail] => customer@mail.com
    [languageId] => ITA
    [session_id] => SESSID123
    [mac] => 0fa0ca05a13c6b5d0bd1466461319658f7f990bf
)

STR;
        $logger->debug($log)->shouldBeCalled();

        $expectedUrl = 'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet'.
            '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url=http-succes-url'.
            '&url_back=http-cancel-url&urlpost=http-post-url&mail=customer%40mail.com&languageId=ITA'.
            '&session_id=SESSID123&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf';
        $logger->debug('Generated URL is "' . $expectedUrl . '"')->shouldBeCalled();

        $this
            ->generate(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'sha1',
                50.50,
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

    public function it_should_use_md5_method_if_specified(): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\DefaultUrlGenerator');
        $this
            ->generate(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'md5',
                50.50,
                '1200123',
                'http-cancel-url',
                'customer@mail.com',
                'http-succes-url',
                'SESSID123',
                'ITA',
                'http-post-url'
            )
            ->shouldReturn(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet'.
                '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url=http-succes-url'.
                '&url_back=http-cancel-url&urlpost=http-post-url&mail=customer%40mail.com'.
                '&languageId=ITA&session_id=SESSID123&mac=ZjkyM2NhY2I0M2YyYTA4Y2ViMTEwZDFjZTY5MjE5Zjk%253D'
            )
        ;
    }

    public function it_should_throw_an_invalid_argument_exception_if_mac_method_is_not_sha1_or_md5(): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'generate',
                array(
                    'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                    'merchant_alias',
                    'secret_key',
                    'invalid', // <- Invalid MAC method
                    50.50,
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

    public function it_should_throw_an_invalid_argument_exception_if_amount_has_more_than_two_decimal(): void
    {
        $this
            ->shouldThrow(\RuntimeException::class)
            ->during(
                'generate',
                array(
                    'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                    'merchant_alias',
                    'secret_key',
                    'sha1',
                    50.505, // <- Invalid amount
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

    public function it_is_initializable_and_generates_correct_url_with_selectedcard_value(): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\DefaultUrlGenerator');
        $this
            ->generate(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                'merchant_alias',
                'secret_key',
                'sha1',
                50.50,
                '1200123',
                'http-cancel-url',
                'customer@mail.com',
                'http-succes-url',
                'SESSID123',
                'ITA',
                'http-post-url',
                'SCT'
            )
            ->shouldReturn(
                'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet'.
                '?alias=merchant_alias&importo=5050&divisa=EUR&codTrans=1200123&url=http-succes-url'.
                '&url_back=http-cancel-url&urlpost=http-post-url&mail=customer%40mail.com'.
                '&languageId=ITA&session_id=SESSID123&selectedcard=SCT&mac=0fa0ca05a13c6b5d0bd1466461319658f7f990bf'
            )
        ;
    }

    public function it_throws_error_if_an_unexpected_selectedcard_value_is_received(): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\PaymentInit\\DefaultUrlGenerator');
        $this
            ->shouldThrow(\RuntimeException::class)
            ->during(
                'generate',
                array(
                    'https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet',
                    'merchant_alias',
                    'secret_key',
                    'sha1',
                    50.50,
                    '1200123',
                    'http-cancel-url',
                    'customer@mail.com',
                    'http-succes-url',
                    'SESSID123',
                    'ITA',
                    'http-post-url',
                    'KRAKENPAY'
                )
            )
        ;
    }
}
