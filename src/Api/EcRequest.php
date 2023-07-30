<?php

namespace Webgriffe\LibQuiPago\Api;

use DOMDocument;
use GuzzleHttp\Psr7\Request;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

class EcRequest
{
    public const OPERATION_TYPE_CAPTURE = 'P';

    public const OPERATION_TYPE_VOID = 'R';

    public const REQUEST_TYPE_FIRST_ATTEMPT = 'FA';

    public const REQUEST_TYPE_RETRY_ATTEMPT = 'RA';

    private string $originalAmount;

    private string $operationAmount;

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
        private $operationType,
        private $merchantAlias,
        private $macKey,
        private $transactionCode,
        private $requestType,
        private $operationId,
        $originalAmount,
        private $currency,
        private $authCode,
        $operationAmount,
        private $user,
        private $isTest
    ) {
        $this->originalAmount = $this->convertAmountToString($originalAmount);
        $this->operationAmount = $this->convertAmountToString($operationAmount);

        $this->validate();
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
     */
    public static function createVoidRequest(
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
    ): EcRequest
    {
        return new EcRequest(
            self::OPERATION_TYPE_VOID,
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
    ): EcRequest
    {
        return new EcRequest(
            self::OPERATION_TYPE_CAPTURE,
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

    public function getUrl(): string
    {
        if (!$this->isTest) {
            return 'https://ecommerce.nexi.it/ecomm/ecomm/XPayBo';
        }

        return 'https://int-ecommerce.nexi.it/ecomm/ecomm/XPayBo';
    }

    public function getBody(): string|bool
    {
        $domDocument = new DOMDocument('1.0', 'ISO-8859-15');
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

    public function asPsrRequest(): Request
    {
        return new Request('POST', $this->getUrl(), [], $this->getBody());
    }

    private function calculateMac(): string
    {
        $macString = implode(
            '',
            [$this->merchantAlias, $this->transactionCode, $this->operationId, $this->operationType, $this->originalAmount, $this->currency, $this->authCode, $this->operationAmount, $this->user, $this->macKey]
        );
        return sha1($macString);
    }

    /**
     * @param float $amount
     */
    private function convertAmountToString($amount): string
    {
        return str_pad((string)round($amount, 2)*100, 9, '0', STR_PAD_LEFT);
    }

    private function validate()
    {
        try {
            $validator = v::attribute('merchantAlias', v::stringType()->alnum('_')->noWhitespace()->length(1, 30))
                ->attribute('macKey', v::stringType()->length(1))
                ->attribute('transactionCode', v::stringType()->alnum()->noWhitespace()->length(1, 30))
                ->attribute(
                    'requestType',
                    v::oneOf(
                        v::stringType()->equals(self::REQUEST_TYPE_FIRST_ATTEMPT),
                        v::stringType()->equals(self::REQUEST_TYPE_RETRY_ATTEMPT)
                    )
                )
                ->attribute('operationId', v::stringType()->digit()->noWhitespace()->length(1, 10))
                ->attribute(
                    'operationType',
                    v::oneOf(
                        v::stringType()->equals(self::OPERATION_TYPE_CAPTURE),
                        v::stringType()->equals(self::OPERATION_TYPE_VOID)
                    )
                )
                ->attribute('originalAmount', v::stringType()->digit()->noWhitespace()->length(9, 9))
                ->attribute('currency', v::stringType()->alnum()->noWhitespace()->length(3, 3))
                ->attribute('authCode', v::stringType()->alnum()->noWhitespace()->length(1, 10))
                ->attribute('operationAmount', v::stringType()->digit()->noWhitespace()->length(9, 9))
                ->attribute('user', v::optional(v::stringType()->alnum(' ')->length(0, 20)))
                ->attribute('isTest', v::boolType());
            $validator->assert($this);
        } catch (NestedValidationException $nestedValidationException) {
            throw new ValidationException($nestedValidationException->getFullMessage());
        }
    }
}
