<?php

namespace spec\Webgriffe\LibQuiPago\Api;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Webgriffe\LibQuiPago\Api\EcResponse;
use Webgriffe\LibQuiPago\Api\ValidationException;

class EcResponseSpec extends ObjectBehavior
{
    private $macKey = '123key';

    function it_is_initializable_through_psr_response(ResponseInterface $response)
    {
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
    }

    function it_should_return_body_from_psr_response(ResponseInterface $response)
    {
        $body = $this->get_positive_response_body();
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->getRawBody()->shouldBeEqualTo($body);
    }

    function it_should_handle_positive_response(ResponseInterface $response)
    {
        $body = $this->get_positive_response_body();
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->isPositive()->shouldReturn(true);
        $this->getErrorMessageByResultCode()->shouldReturn(null);
    }

    function it_should_throw_an_exception_if_invalid_response(ResponseInterface $response)
    {
        $response->getBody()->willReturn('invalid body');
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $exception = new ValidationException(
            'The string "invalid body" is an invalid EcResponse body. String could not be parsed as XML'
        );
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_throw_an_exception_if_valid_xml_body_but_not_valid_mac(ResponseInterface $response)
    {
        $body = $this->get_positive_response_body();
        $body = str_replace('<mac>dece8354cb73bc31224f10747e085909b9752c13</mac>', '<mac>invalid</mac>', $body);
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $exception = new ValidationException(
            'Invalid MAC code in EcResponse body. ' .
            'Expected MAC was "dece8354cb73bc31224f10747e085909b9752c13", "invalid" given. Raw body is "' .
            $body . '".'
        );
        $this->shouldThrow($exception)->duringInstantiation();
    }

    function it_should_not_validate_mac_if_it_is_empty(ResponseInterface $response)
    {
        $body = $this->get_positive_response_body();
        $body = str_replace('<mac>dece8354cb73bc31224f10747e085909b9752c13</mac>', '<mac></mac>', $body);
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->shouldHaveType(EcResponse::class);
        $this->getMac()->shouldReturn('');
    }

    function it_should_return_error_message_in_case_of_negative_result(ResponseInterface $response)
    {
        $body = $this->get_positive_response_body();
        $body = str_replace('<esitoRichiesta>0</esitoRichiesta>', '<esitoRichiesta>1</esitoRichiesta>', $body);
        $response->getBody()->willReturn($body);
        $this->beConstructedThrough('createFromPsrResponse', array($response, $this->macKey));
        $this->isPositive()->shouldReturn(false);
        $this->getErrorMessageByResultCode()->shouldReturn(
            'Errore nella richiesta: Formato del messaggio errato o campo mancante o errato'
        );
    }

    private function get_positive_response_body()
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
