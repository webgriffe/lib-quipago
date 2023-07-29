<?php

namespace spec\Webgriffe\LibQuiPago\Api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Webgriffe\LibQuiPago\Api\Client;
use Webgriffe\LibQuiPago\Api\EcRequest;
use Webgriffe\LibQuiPago\Api\EcResponse;

class ClientSpec extends ObjectBehavior
{
    private string $merchantAlias = 'ALIAS123123';

    private string $macKey = 'KEY123123';

    private string $user = 'John Doe';

    private string $transactionCode = '00000123';

    private string $requestType = EcRequest::REQUEST_TYPE_FIRST_ATTEMPT;

    private string $operationId = '1213123';

    private float $originalAmount = 230.78;

    private string $currency = 'EUR';

    private string $authCode = '00901';

    private int $operationAmount = 200;

    public function it_is_initializable(ClientInterface $client): void
    {
        $this->beConstructedWith($client, $this->merchantAlias, $this->macKey, $this->user);
        $this->shouldHaveType(Client::class);
        $this->getMerchantAlias()->shouldReturn($this->merchantAlias);
        $this->getMacKey()->shouldReturn($this->macKey);
        $this->getUser()->shouldReturn($this->user);
    }

    public function it_should_capture_funds(ClientInterface $client, ResponseInterface $response): void
    {
        $response->getBody()->willReturn($this->get_positive_capture_response_body());
        $client->send(Argument::type(RequestInterface::class))->shouldBeCalled()->willReturn($response);

        $this->beConstructedWith($client, $this->merchantAlias, $this->macKey, $this->user);

        $response = $this->capture(
            $this->transactionCode,
            $this->requestType,
            $this->operationId,
            $this->originalAmount,
            $this->currency,
            $this->authCode,
            $this->operationAmount,
            false // $isTest
        );
        $response->shouldHaveType(EcResponse::class);
        $response->isPositive()->shouldReturn(true);
    }

    public function it_should_disable_ssl_verify_when_is_test(ClientInterface $client, ResponseInterface $response): void
    {
        $response->getBody()->willReturn($this->get_positive_capture_response_body());
        $client
            ->send(Argument::type(RequestInterface::class), [RequestOptions::VERIFY => false])
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $this->beConstructedWith($client, $this->merchantAlias, $this->macKey, $this->user);

        $response = $this->capture(
            $this->transactionCode,
            $this->requestType,
            $this->operationId,
            $this->originalAmount,
            $this->currency,
            $this->authCode,
            $this->operationAmount,
            true // $isTest
        );
        $response->shouldHaveType(EcResponse::class);
        $response->isPositive()->shouldReturn(true);
    }

    public function it_should_void_transaction(ClientInterface $client, ResponseInterface $response): void
    {
        $response->getBody()->willReturn($this->get_positive_void_response_body());
        $client->send(Argument::type(RequestInterface::class))->shouldBeCalled()->willReturn($response);

        $this->beConstructedWith($client, $this->merchantAlias, $this->macKey, $this->user);

        $response = $this->void(
            $this->transactionCode,
            $this->requestType,
            $this->operationId,
            $this->originalAmount,
            $this->currency,
            $this->authCode,
            $this->operationAmount,
            false // $isTest
        );
        $response->shouldHaveType(EcResponse::class);
        $response->isPositive()->shouldReturn(true);
    }

    private function get_raw_operation_amount(): string
    {
        return str_pad((string)round($this->operationAmount, 2)*100, 9, '0', STR_PAD_LEFT);
    }

    private function get_positive_capture_response_body(): string
    {
        return <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?>
<VPOSRES>
<alias>$this->merchantAlias</alias>
<ECRES>
<codTrans>$this->transactionCode</codTrans>
<request_type>$this->requestType</request_type>
<esitoRichiesta>0</esitoRichiesta>
<id_op>$this->operationId</id_op>
<type_op>P</type_op>
<importo_op>{$this->get_raw_operation_amount()}</importo_op>
</ECRES>
<mac>130c771cc795ab918de6a9ee014c0145a2acb918</mac>
</VPOSRES>
XML;
    }

    private function get_positive_void_response_body(): string
    {
        return <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?>
<VPOSRES>
<alias>$this->merchantAlias</alias>
<ECRES>
<codTrans>$this->transactionCode</codTrans>
<request_type>$this->requestType</request_type>
<esitoRichiesta>0</esitoRichiesta>
<id_op>$this->operationId</id_op>
<type_op>R</type_op>
<importo_op>{$this->get_raw_operation_amount()}</importo_op>
</ECRES>
<mac>05be1e959c83f4f347681b458ea4fa184840b3c0</mac>
</VPOSRES>
XML;
    }
}
