<?php

namespace spec\Webgriffe\LibQuiPago\Api;

use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Webgriffe\LibQuiPago\Api\EcResponse;

class ClientSpec extends ObjectBehavior
{
    private $merchantAlias = 'ALIAS123123';
    private $macKey = 'KEY123123';
    private $user = 'John Doe';

    function it_is_initializable(ClientInterface $client)
    {
        $this->beConstructedWith($client, $this->merchantAlias, $this->macKey, $this->user);
        $this->shouldHaveType('Webgriffe\LibQuiPago\Api\Client');
        $this->getMerchantAlias()->shouldReturn($this->merchantAlias);
        $this->getMacKey()->shouldReturn($this->macKey);
        $this->getUser()->shouldReturn($this->user);
    }

    function it_should_capture_funds(
        ClientInterface $client,
        ResponseInterface $response
    ) {
        $response->getBody()->willReturn($this->get_positive_response_body());
        $client->send(Argument::type(RequestInterface::class))->shouldBeCalled()->willReturn($response);

        $this->beConstructedWith($client, $this->merchantAlias, $this->macKey, $this->user);

        $transactionCode = '00000123';
        $operationType = 'FA';
        $operationId = '1213123';
        $originalAmount = 230.78;
        $currency = 'EUR';
        $authCode = '00901';
        $operationAmount = 200;
        $isTest = false;
        $this
            ->capture(
                $transactionCode,
                $operationType,
                $operationId,
                $originalAmount,
                $currency,
                $authCode,
                $operationAmount,
                $isTest
            )
            ->shouldHaveType(EcResponse::class)
        ;
    }

    private function get_positive_response_body()
    {
        return <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?>
<VPOSRES>
<alias>ALIAS123123</alias>
<ECRES>
<codTrans>00000123</codTrans>
<request_type>FA</request_type>
<esitoRichiesta>0</esitoRichiesta>
<id_op>1213123</id_op>
<type_op>C</type_op>
<importo_op>000020000</importo_op>
</ECRES>
<mac>605ce3e17f66c7c78f73cb03c75f05aea048c1c2</mac>
</VPOSRES>
XML;
    }
}
