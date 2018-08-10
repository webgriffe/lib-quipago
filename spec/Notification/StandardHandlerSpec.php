<?php

namespace spec\Webgriffe\LibQuiPago\Notification;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\InvalidMacException;
use Psr\Http\Message\ServerRequestInterface;

class StandardHandlerSpec extends ObjectBehavior
{
    function it_throws_an_exception_if_some_parameter_is_missing(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $requestRawParams = $this->getRequestRawParams();
        unset($requestRawParams['alias']);
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    function it_throws_an_exception_if_amount_is_not_numeric(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['importo'] = 'ABC';
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    function it_throws_an_exception_if_amount_is_not_an_integer_number(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['importo'] = '100.10';
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    function it_throws_an_exception_if_mac_is_wrong(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'invalid-mac';
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(InvalidMacException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    function it_returns_mapped_params(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $request->getParsedBody()->willReturn($this->getRequestRawParams());
        $result = $this->handle($request, 'secret_key', 'sha1');

        $result->getTransactionCode()->shouldReturn('1200123');
        $result->isTransactionResultPositive()->shouldReturn(true);
        $result->getAmount()->shouldReturn(50.50);
        $result->getCurrency()->shouldReturn('EUR');
        $result->getDate()->shouldHaveType(\DateTime::class);
        $result->getDate()->format('d/m/Y H:i:s')->shouldReturn('21/02/2016 18:18:54');
        $result->getAuthCode()->shouldReturn('123abc');
        $result->getMerchantAlias()->shouldReturn('merchant_123');
        $result->getSessionId()->shouldReturn('123123');
        $result->getCardBrand()->shouldReturn('Visa');
        $result->getFirstName()->shouldReturn('John');
        $result->getLastName()->shouldReturn('Doe');
        $result->getEmail()->shouldReturn('jd@mail.com');
    }

    function it_returns_negative_result_if_transaction_result_is_ko(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['esito'] = 'KO';
        $requestRawParams['mac'] = '5246a2f5d8dce9a0cabb4c4369cf69c4b567647e';
        $request->getParsedBody()->willReturn($requestRawParams);

        $result = $this->handle($request,'secret_key', 'sha1');
        $result->isTransactionResultPositive()->shouldReturn(false);
    }

    function it_should_log_if_logger_is_passed(ServerRequestInterface $request, LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $logger->debug('Webgriffe\\LibQuiPago\\Notification\\StandardHandler::handle method called')->shouldBeCalled();
        $logger->debug('Secret key: "secret_key"')->shouldBeCalled();
        $logger->debug(sprintf('Request params: %s', json_encode($this->getRequestRawParams())))->shouldBeCalled();

        $str = 'codTrans=1200123esito=OKimporto=5050divisa=EURdata=20160221orario=181854codAut=123abc';
        $logger->debug("MAC calculation string is \"$str\"")->shouldBeCalled();

        $logger->debug('MAC calculation method is "sha1"')->shouldBeCalled();
        $logger->debug(Argument::containingString('Calculated MAC is "'))->shouldBeCalled();
        $logger->debug(Argument::containingString('MAC from request is "'))->shouldBeCalled();
        $logger->debug('MAC from request is valid')->shouldBeCalled();

        $request->getParsedBody()->willReturn($this->getRequestRawParams());

        $this->handle($request,'secret_key', 'sha1');
    }

    function it_should_use_md5_as_mac_calculation_method_if_specified(ServerRequestInterface $request)
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\StandardHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'ZjM0OWQzN2MwOTMzYTk3Y2Y5MjIyOTBiM2FmYTM0ZjI%3D';
        $request->getParsedBody()->willReturn($requestRawParams);

        $result = $this->handle($request, 'secret_key', 'md5');

        $result->getTransactionCode()->shouldReturn('1200123');
        $result->isTransactionResultPositive()->shouldReturn(true);
        $result->getAmount()->shouldReturn(50.50);
        $result->getCurrency()->shouldReturn('EUR');
        $result->getDate()->shouldHaveType(\DateTime::class);
        $result->getDate()->format('d/m/Y H:i:s')->shouldReturn('21/02/2016 18:18:54');
        $result->getAuthCode()->shouldReturn('123abc');
        $result->getMerchantAlias()->shouldReturn('merchant_123');
        $result->getSessionId()->shouldReturn('123123');
        $result->getCardBrand()->shouldReturn('Visa');
        $result->getFirstName()->shouldReturn('John');
        $result->getLastName()->shouldReturn('Doe');
        $result->getEmail()->shouldReturn('jd@mail.com');
    }

    /**
     * @return array
     */
    private function getRequestRawParams()
    {
        return array(
            'codTrans'      => '1200123',
            'esito'         => 'OK',
            'importo'       => '5050',
            'divisa'        => 'EUR',
            'data'          => '20160221',
            'orario'        => '181854',
            'codAut'        => '123abc',
            'mac'           => 'c83cee2a5422189cab2b54ef685b29dc428741dc',
            'alias'         => 'merchant_123',
            'session_id'    => '123123',
            '$BRAND'        => 'Visa',
            'nome'          => 'John',
            'cognome'       => 'Doe',
            'mail'          => 'jd@mail.com',
        );
    }
}
