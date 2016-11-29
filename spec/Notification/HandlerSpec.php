<?php

namespace spec\Webgriffe\LibQuiPago\Notification;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Notification\InvalidMacException;

class HandlerSpec extends ObjectBehavior
{
    function it_throws_an_exception_if_some_parameter_is_missing()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        unset($requestRawParams['alias']);
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array('secret_key', 'sha1', $requestRawParams)
        );
    }

    function it_throws_an_exception_if_amount_is_not_numeric()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['importo'] = 'ABC';
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array('secret_key', 'sha1', $requestRawParams)
        );
    }

    function it_throws_an_exception_if_amount_is_not_an_integer_number()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['importo'] = '100.10';
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array('secret_key', 'sha1', $requestRawParams)
        );
    }

    function it_throws_an_exception_if_mac_is_wrong()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'invalid-mac';
        $this->shouldThrow(InvalidMacException::class)->during(
            'handle',
            array('secret_key', 'sha1', $requestRawParams)
        );
    }

    function it_returns_mapped_params()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $this->handle('secret_key', 'sha1', $this->getRequestRawParams());

        $this->getTransactionCode()->shouldReturn('1200123');
        $this->isTransactionResultPositive()->shouldReturn(true);
        $this->getAmount()->shouldReturn(50.50);
        $this->getCurrency()->shouldReturn('EUR');
        $this->getTransactionDate()->shouldHaveType(\DateTime::class);
        $this->getTransactionDate()->format('d/m/Y H:i:s')->shouldReturn('21/02/2016 18:18:54');
        $this->getAuthCode()->shouldReturn('123abc');
        $this->getMacFromRequest()->shouldReturn('c83cee2a5422189cab2b54ef685b29dc428741dc');
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
        $requestRawParams['mac'] = '5246a2f5d8dce9a0cabb4c4369cf69c4b567647e';
        $this->handle('secret_key', 'sha1', $requestRawParams);
        $this->isTransactionResultPositive()->shouldReturn(false);
    }

    function it_should_log_if_logger_is_passed(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $logger->debug('Webgriffe\\LibQuiPago\\Notification\\Handler::handle method called')->shouldBeCalled();
        $logger->debug('Secret key: "secret_key"')->shouldBeCalled();
        $logger->debug(sprintf('Request params: %s', json_encode($this->getRequestRawParams())))->shouldBeCalled();
        $str = 'codTrans=1200123esito=OKimporto=5050divisa=EURdata=20160221orario=181854codAut=123abcsecret_key';
        $logger->debug("MAC calculation string is \"$str\"")->shouldBeCalled();
        $logger->debug('MAC calculation method is "sha1"')->shouldBeCalled();
        $this->handle('secret_key', 'sha1', $this->getRequestRawParams());
    }

    function it_should_use_md5_as_mac_calculation_method_if_specified()
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\Handler');
        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'f349d37c0933a97cf922290b3afa34f2';
        $this->handle('secret_key', 'md5', $requestRawParams);

        $this->getTransactionCode()->shouldReturn('1200123');
        $this->isTransactionResultPositive()->shouldReturn(true);
        $this->getAmount()->shouldReturn(50.50);
        $this->getCurrency()->shouldReturn('EUR');
        $this->getTransactionDate()->shouldHaveType(\DateTime::class);
        $this->getTransactionDate()->format('d/m/Y H:i:s')->shouldReturn('21/02/2016 18:18:54');
        $this->getAuthCode()->shouldReturn('123abc');
        $this->getMacFromRequest()->shouldReturn('f349d37c0933a97cf922290b3afa34f2');
        $this->getMerchantAlias()->shouldReturn('merchant_123');
        $this->getSessionId()->shouldReturn('123123');
        $this->getCardBrand()->shouldReturn('Visa');
        $this->getFirstName()->shouldReturn('John');
        $this->getLastName()->shouldReturn('Doe');
        $this->getEmail()->shouldReturn('jd@mail.com');
    }

    /**
     * @return array
     */
    private function getRequestRawParams()
    {
        return array(
            'codTrans' => '1200123',
            'esito' => 'OK',
            'importo' => '5050',
            'divisa' => 'EUR',
            'data' => '20160221',
            'orario' => '181854',
            'codAut' => '123abc',
            'mac' => 'c83cee2a5422189cab2b54ef685b29dc428741dc',
            'alias' => 'merchant_123',
            'session_id' => '123123',
            '$BRAND' => 'Visa',
            'nome' => 'John',
            'cognome' => 'Doe',
            'mail' => 'jd@mail.com',
        );
    }
}
