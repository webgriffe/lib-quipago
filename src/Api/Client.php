<?php

namespace Webgriffe\LibQuiPago\Api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class Client
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $merchantAlias,
        private string $macKey,
        private string $user,
    ) {
    }

    public function getMerchantAlias(): string
    {
        return $this->merchantAlias;
    }

    public function getMacKey(): string
    {
        return $this->macKey;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function capture(
        string $transactionCode,
        string $requestType,
        string $operationId,
        float $originalAmount,
        string $currency,
        string $authCode,
        float $operationAmount,
        bool $isTest,
    ): EcResponse {
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

    public function void(
        string $transactionCode,
        string $requestType,
        string $operationId,
        float $originalAmount,
        string $currency,
        string $authCode,
        float $operationAmount,
        bool $isTest,
    ): EcResponse {
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
