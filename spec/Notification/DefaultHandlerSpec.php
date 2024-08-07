<?php

namespace spec\Webgriffe\LibQuiPago\Notification;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\InvalidMacException;
use Psr\Http\Message\ServerRequestInterface;

class DefaultHandlerSpec extends ObjectBehavior
{
    public function it_throws_an_exception_if_some_parameter_is_missing(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $requestRawParams = $this->getRequestRawParams();
        unset($requestRawParams['alias']);
        $request->getMethod()->willReturn('POST');
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    public function it_throws_an_exception_if_amount_is_not_numeric(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['importo'] = 'ABC';
        $request->getMethod()->willReturn('POST');
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    public function it_throws_an_exception_if_amount_is_not_an_integer_number(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['importo'] = '100.10';
        $request->getMethod()->willReturn('POST');
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    public function it_throws_an_exception_if_mac_is_wrong(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'invalid-mac';
        $request->getMethod()->willReturn('POST');
        $request->getParsedBody()->willReturn($requestRawParams);

        $this->shouldThrow(InvalidMacException::class)->during(
            'handle',
            array($request, 'secret_key', 'sha1')
        );
    }

    public function it_returns_mapped_params(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $request->getMethod()->willReturn('POST');
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

    public function it_returns_mapped_params_from_query_string(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $request->getQueryParams()->willReturn($this->getRequestRawParams());
        $request->getMethod()->willReturn('GET');
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

    public function it_returns_negative_result_if_transaction_result_is_ko(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['esito'] = 'KO';
        $requestRawParams['mac'] = '5246a2f5d8dce9a0cabb4c4369cf69c4b567647e';
        $request->getMethod()->willReturn('POST');
        $request->getParsedBody()->willReturn($requestRawParams);

        $result = $this->handle($request,'secret_key', 'sha1');
        $result->isTransactionResultPositive()->shouldReturn(false);
    }

    public function it_should_log_if_logger_is_passed(ServerRequestInterface $request, LoggerInterface $logger): void
    {
        $this->beConstructedWith($logger);
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $logger->debug('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler::handle method called')->shouldBeCalled();
        $logger->debug('Secret key: "secret_key"')->shouldBeCalled();
        $logger->debug(sprintf('Request body: %s', json_encode($this->getRequestRawParams())))->shouldBeCalled();
        $logger->debug(sprintf('Request query: []'))->shouldBeCalled();

        $str = 'codTrans=1200123esito=OKimporto=5050divisa=EURdata=20160221orario=181854codAut=123abc';
        $logger->debug("MAC calculation string is \"$str\"")->shouldBeCalled();

        $logger->debug('MAC calculation method is "sha1"')->shouldBeCalled();
        $logger->debug(Argument::containingString('Calculated MAC is "'))->shouldBeCalled();
        $logger->debug(Argument::containingString('MAC from request is "'))->shouldBeCalled();
        $logger->debug('MAC from request is valid')->shouldBeCalled();

        $request->getMethod()->willReturn('POST');
        $request->getParsedBody()->willReturn($this->getRequestRawParams());
        $request->getQueryParams()->willReturn([]);

        $this->handle($request,'secret_key', 'sha1');
    }

    public function it_should_use_md5_as_mac_calculation_method_if_specified(ServerRequestInterface $request): void
    {
        $this->shouldHaveType('Webgriffe\\LibQuiPago\\Notification\\DefaultHandler');

        $requestRawParams = $this->getRequestRawParams();
        $requestRawParams['mac'] = 'ZjM0OWQzN2MwOTMzYTk3Y2Y5MjIyOTBiM2FmYTM0ZjI%3D';
        $request->getMethod()->willReturn('POST');
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

    private function getRequestRawParams(): array
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
