<?php

namespace Webgriffe\LibQuiPago\Api;

use GuzzleHttp\Psr7\Request;
use Respect\Validation\Validator as v;

class EcRequest
{
    const CAPTURE_OPERATION_TYPE = 'P';
    const VOID_OPERATION_TYPE = 'R';

    /**
     * @var string
     */
    private $operationType;
    /**
     * @var string
     */
    private $merchantAlias;
    /**
     * @var string
     */
    private $macKey;
    /**
     * @var string
     */
    private $transactionCode;
    /**
     * @var string
     */
    private $requestType;
    /**
     * @var string
     */
    private $operationId;
    /**
     * @var string
     */
    private $originalAmount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $authCode;
    /**
     * @var string
     */
    private $operationAmount;
    /**
     * @var string
     */
    private $user;
    /**
     * @var bool
     */
    private $isTest;

    /**
     * EcRequest constructor.
     * @param string $operationType
     * @param string $merchantAlias
     * @param string $macKey
     * @param string $transactionCode
     * @param string $requestType
     * @param string $operationId
     * @param float $originalAmount
     * @param string $currency
     * @param string $authCode
     * @param float $operationAmount
     * @param string $user
     * @param bool $isTest
     */
    private function __construct(
        $operationType,
        $merchantAlias,
        $macKey,
        $transactionCode,
        $requestType,
        $operationId,
        $originalAmount,
        $currency,
        $authCode,
        $operationAmount,
        $user,
        $isTest
    ) {
        $this->operationType = $operationType;
        $this->merchantAlias = $merchantAlias;
        $this->macKey = $macKey;
        $this->transactionCode = $transactionCode;
        $this->requestType = $requestType;
        $this->operationId = $operationId;
        $this->originalAmount = $this->convertAmountToString($originalAmount);
        $this->currency = $currency;
        $this->authCode = $authCode;
        $this->operationAmount = $this->convertAmountToString($operationAmount);
        $this->user = $user;
        $this->isTest = $isTest;

        $validator = v::attribute('merchantAlias', v::stringType()->alnum('_')->noWhitespace()->length(1, 30))
            ->attribute('macKey', v::stringType()->length(1))
            ->attribute('transactionCode', v::stringType()->alnum()->noWhitespace()->length(1, 30))
            ->attribute('requestType', v::oneOf(v::stringType()->equals('FA'), v::stringType()->equals('RA')))
            ->attribute('operationId', v::stringType()->digit()->noWhitespace()->length(1, 10))
            ->attribute(
                'operationType',
                v::oneOf(
                    v::stringType()->equals(self::CAPTURE_OPERATION_TYPE),
                    v::stringType()->equals(self::VOID_OPERATION_TYPE)
                )
            )
            ->attribute('originalAmount', v::stringType()->digit()->noWhitespace()->length(9, 9))
            ->attribute('currency', v::stringType()->alnum()->noWhitespace()->length(3, 3))
            ->attribute('authCode', v::stringType()->alnum()->noWhitespace()->length(1, 10))
            ->attribute('operationAmount', v::stringType()->digit()->noWhitespace()->length(9, 9))
            ->attribute('user', v::optional(v::stringType()->alnum()->length(0, 20)))
            ->attribute('isTest', v::boolType())
        ;
        $validator->assert($this);
    }

    /**
     * @param string $merchantAlias
     * @param string $macKey
     * @param string $transactionCode
     * @param string $requestType
     * @param string $operationId
     * @param float $originalAmount
     * @param string $currency
     * @param string $authCode
     * @param float $operationAmount
     * @param string $user
     * @param bool $isTest
     * @return EcRequest
     */
    public static function createCaptureRequest(
        $merchantAlias,
        $macKey,
        $transactionCode,
        $requestType,
        $operationId,
        $originalAmount,
        $currency,
        $authCode,
        $operationAmount,
        $user = '',
        $isTest = false
    ) {
        return new EcRequest(
            self::CAPTURE_OPERATION_TYPE,
            $merchantAlias,
            $macKey,
            $transactionCode,
            $requestType,
            $operationId,
            $originalAmount,
            $currency,
            $authCode,
            $operationAmount,
            $user,
            $isTest
        );
    }

    /**
     * @return string
     */
    public function getMerchantAlias()
    {
        return $this->merchantAlias;
    }

    public function getUrl()
    {
        if (!$this->isTest) {
            return 'https://ecommerce.keyclient.it/ecomm/ecomm/XPayBo';
        }
        return 'https://coll-ecommerce.keyclient.it/ecomm/ecomm/XPayBo';
    }

    public function getBody()
    {
        $domDocument = new \DOMDocument('1.0', 'ISO-8859-15');
        $domDocument->formatOutput = true;
        $vposreq = $domDocument->createElement('VPOSREQ');
        $vposreq->appendChild($domDocument->createElement('alias', $this->merchantAlias));
        $ecreq = $domDocument->createElement('ECREQ');
        $ecreq->appendChild($domDocument->createElement('codTrans', $this->transactionCode));
        $ecreq->appendChild($domDocument->createElement('request_type', $this->requestType));
        $ecreq->appendChild($domDocument->createElement('id_op', $this->operationId));
        $ecreq->appendChild($domDocument->createElement('type_op', $this->operationType));
        $ecreq->appendChild($domDocument->createElement('importo', $this->originalAmount));
        $ecreq->appendChild($domDocument->createElement('divisa', $this->currency));
        $ecreq->appendChild($domDocument->createElement('codAut', $this->authCode));
        $ecreq->appendChild($domDocument->createElement('importo_op', $this->operationAmount));
        $vposreq->appendChild($ecreq);
        $vposreq->appendChild($domDocument->createElement('user', $this->user));
        $vposreq->appendChild($domDocument->createElement('mac', $this->calculateMac()));
        $domDocument->appendChild($vposreq);
        return $domDocument->saveXML();
    }

    public function asPsrRequest()
    {
        return new Request('POST', $this->getUrl(), [], $this->getBody());
    }

    private function calculateMac()
    {
        $macString = implode(
            '',
            array(
                $this->merchantAlias,
                $this->transactionCode,
                $this->operationId,
                $this->operationType,
                $this->originalAmount,
                $this->currency,
                $this->authCode,
                $this->operationAmount,
                $this->user,
                $this->macKey
            )
        );
        return sha1($macString);
    }

    /**
     * @param float $amount
     * @return string
     */
    private function convertAmountToString($amount)
    {
        return str_pad((string)round($amount, 2)*100, 9, '0', STR_PAD_LEFT);
    }
}
