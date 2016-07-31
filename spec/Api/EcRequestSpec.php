<?php

namespace spec\Webgriffe\LibQuiPago\Api;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Webgriffe\LibQuiPago\Api\EcRequest;

class EcRequestSpec extends ObjectBehavior
{
    function it_is_initializable_as_capture_request()
    {
        $this->beConstructedThrough('createCaptureRequest', $this->get_valid_capture_request_data());
        $this->shouldHaveType(EcRequest::class);
        $this->getUrl()->shouldReturn('https://ecommerce.keyclient.it/ecomm/ecomm/XPayBo');

        $body = <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?>
<VPOSREQ>
  <alias>0000000050242004</alias>
  <ECREQ>
    <codTrans>T0000000000000000001</codTrans>
    <request_type>FA</request_type>
    <id_op>0000000001</id_op>
    <type_op>P</type_op>
    <importo>000123056</importo>
    <divisa>978</divisa>
    <codAut>098765</codAut>
    <importo_op>000120056</importo_op>
  </ECREQ>
  <user>User001</user>
  <mac>5240fa1bc9c57d5cca1c36dc197cfcae889598cb</mac>
</VPOSREQ>

XML;
        $this->getBody()->shouldReturn($body);
    }

    function it_should_return_itself_as_psr_request()
    {
        $this->beConstructedThrough('createCaptureRequest', $this->get_valid_capture_request_data());
        $this->shouldHaveType(EcRequest::class);
        /** @var RequestInterface $psrRequest */
        $psrRequest = $this->asPsrRequest();
        $psrRequest->shouldHaveType(RequestInterface::class);
        $psrRequest->getMethod()->shouldReturn('POST');
        $psrRequest->getBody()->__toString()->shouldReturn($this->getBody());
        $psrRequest->getUri()->__toString()->shouldReturn($this->getUrl());
    }

    function it_should_throw_an_exception_if_data_are_not_valid()
    {
        $data = $this->get_valid_capture_request_data();
        $data[0] = '...'; // Not valid
        $this->beConstructedThrough('createCaptureRequest', $data);
        $this->shouldThrow(ExceptionInterface::class)->duringInstantiation();
    }

    /**
     * @return array
     */
    private function get_valid_capture_request_data()
    {
        return [
            '0000000050242004', // $merchantAlias,
            'QKXQWGUFCKBQYHOPBNJTM', //$macKey,
            'T0000000000000000001', //$transactionCode,
            'FA', //$requestType,
            '0000000001', //$operationId,
            1230.56, //$originalAmount,
            '978', //$currency,
            '098765', //$authCode,
            1200.56, //$operationAmount,
            'User001', //$user,
            false, //$isTest,
        ];
    }
}
