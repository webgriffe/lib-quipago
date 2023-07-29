<?php

namespace Webgriffe\LibQuiPago\Api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class Client
{
    /**
     * Client constructor.
     * @param string $merchantAlias
     * @param string $macKey
     * @param string $user
     */
    public function __construct(private ClientInterface $httpClient, private $merchantAlias, private $macKey, private $user)
    {
    }

    public function getMerchantAlias()
    {
        return $this->merchantAlias;
    }

    public function getMacKey()
    {
        return $this->macKey;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $transactionCode
     * @param string $requestType
     * @param string $operationId
     * @param float $originalAmount
     * @param string $currency
     * @param string $authCode
     * @param float $operationAmount
     * @param bool $isTest
     *
     * @return EcResponse
     */
    public function capture(
        $transactionCode,
        $requestType,
        $operationId,
        $originalAmount,
        $currency,
        $authCode,
        $operationAmount,
        $isTest
    ) {
        $ecRequest = EcRequest::createCaptureRequest(
            $this->merchantAlias,
            $this->macKey,
            $transactionCode,
            $requestType,
            $operationId,
            $originalAmount,
            $currency,
            $authCode,
            $operationAmount,
            $this->user,
            $isTest
        );
        $options = [];
        if ($isTest) {
            $options = [RequestOptions::VERIFY => false];
        }

        $response = $this->httpClient->send($ecRequest->asPsrRequest(), $options);
        return EcResponse::createFromPsrResponse($response, $this->macKey);
    }

    /**
     * @param string $transactionCode
     * @param string $requestType
     * @param string $operationId
     * @param float $originalAmount
     * @param string $currency
     * @param string $authCode
     * @param float $operationAmount
     * @param bool $isTest
     *
     * @return EcResponse
     */
    public function void(
        $transactionCode,
        $requestType,
        $operationId,
        $originalAmount,
        $currency,
        $authCode,
        $operationAmount,
        $isTest
    ) {
        $ecRequest = EcRequest::createVoidRequest(
            $this->merchantAlias,
            $this->macKey,
            $transactionCode,
            $requestType,
            $operationId,
            $originalAmount,
            $currency,
            $authCode,
            $operationAmount,
            $this->user,
            $isTest
        );
        $options = [];
        if ($isTest) {
            $options = [RequestOptions::VERIFY => false];
        }

        $response = $this->httpClient->send($ecRequest->asPsrRequest(), $options);
        return EcResponse::createFromPsrResponse($response, $this->macKey);
    }
}
