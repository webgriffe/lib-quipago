<?php

namespace spec\Webgriffe\LibQuiPago\Api;

use GuzzleHttp\Psr7\Stream;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Webgriffe\LibQuiPago\Api\EcRequest;
use Webgriffe\LibQuiPago\Api\EcResponse;
use Webgriffe\LibQuiPago\Api\ValidationException;

class EcResponseSpec extends ObjectBehavior
{
    private string $macKey = '123key';

    public function it_is_initializable_through_psr_response(ResponseInterface $response): void
    {
        $body = $this->getResponseBody($this->getPositiveResponseXML());
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
    }

    public function it_should_return_body_from_psr_response(ResponseInterface $response): void
    {
        $body = $this->getResponseBody($this->getPositiveResponseXML());
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->getRawBody()->shouldBeEqualTo($this->getPositiveResponseXML());
    }

    public function it_should_handle_positive_response(ResponseInterface $response): void
    {
        $body = $this->getResponseBody($this->getPositiveResponseXML());
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->isPositive()->shouldReturn(true);
        $this->getErrorMessageByResultCode()->shouldReturn(null);
    }

    public function it_should_throw_an_exception_if_invalid_response(ResponseInterface $response): void
    {
        $response->getBody()->willReturn($this->getResponseBody('invalid body'));
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $exception = new ValidationException(
            'The string "invalid body" is an invalid EcResponse body. String could not be parsed as XML'
        );
        $this->shouldThrow($exception)->duringInstantiation();
    }

    public function it_should_throw_an_exception_if_valid_xml_body_but_not_valid_mac(ResponseInterface $response): void
    {
        $xml = str_replace('<mac>dece8354cb73bc31224f10747e085909b9752c13</mac>', '<mac>invalid</mac>', $this->getPositiveResponseXML());
        $body = $this->getResponseBody($xml);
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $exception = new ValidationException(
            'Invalid MAC code in EcResponse body. ' .
            'Expected MAC was "dece8354cb73bc31224f10747e085909b9752c13", "invalid" given. Raw body is "' .
            $xml . '".'
        );
        $this->shouldThrow($exception)->duringInstantiation();
    }

    public function it_should_not_validate_mac_if_it_is_empty(ResponseInterface $response): void
    {
        $xml = str_replace('<mac>dece8354cb73bc31224f10747e085909b9752c13</mac>', '<mac></mac>', $this->getPositiveResponseXML());
        $body = $this->getResponseBody($xml);
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->getMac()->shouldReturn('');
    }

    public function it_should_return_error_message_in_case_of_negative_result(ResponseInterface $response): void
    {
        $xml = str_replace('<esitoRichiesta>0</esitoRichiesta>', '<esitoRichiesta>1</esitoRichiesta>', $this->getPositiveResponseXML());
        $body = $this->getResponseBody($xml);
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->isPositive()->shouldReturn(false);
        $this->getErrorMessageByResultCode()->shouldReturn(
            'Errore nella richiesta: Formato del messaggio errato o campo mancante o errato'
        );
    }

    public function it_should_return_response_data(ResponseInterface $response): void
    {
        $body = $this->getResponseBody($this->getPositiveResponseXML());
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->getRawBody()->shouldBeEqualTo($this->getPositiveResponseXML());
        $this->getResultCode()->shouldBeEqualTo('0');
        $this->getMerchantAlias()->shouldBeEqualTo('0000000050242004');
        $this->getTransactionCode()->shouldBeEqualTo('T0000000000000000001');
        $this->getRequestType()->shouldBeEqualTo(EcRequest::REQUEST_TYPE_FIRST_ATTEMPT);
        $this->getOperationId()->shouldBeEqualTo('0000000001');
        $this->getOperationType()->shouldBeEqualTo('C');
        $this->getOperationAmountRaw()->shouldBeEqualTo('000120056');
        $this->getOperationAmount()->shouldBeEqualTo(1200.56);
        $this->getMac()->shouldBeEqualTo('dece8354cb73bc31224f10747e085909b9752c13');
    }

    private function getResponseBody(string $body): StreamInterface
    {
        $resource = fopen('data://text/plain,' . $body, 'rb');

        return new Stream($resource);
    }

    private function getPositiveResponseXML(): string
    {
        return <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?>
<VPOSRES>
    <alias>0000000050242004</alias>
    <ECRES>
        <codTrans>T0000000000000000001</codTrans>
        <request_type>FA</request_type>
        <esitoRichiesta>0</esitoRichiesta>
        <id_op>0000000001</id_op>
        <type_op>C</type_op>
        <importo_op>000120056</importo_op>
    </ECRES>
    <mac>dece8354cb73bc31224f10747e085909b9752c13</mac>
</VPOSRES>
XML;
    }
}
