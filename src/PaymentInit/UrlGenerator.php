<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

interface UrlGenerator
{
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param string $gatewayUrl
     * @param string $merchantAlias
     * @param string $secretKey
     * @param string $macMethod
     * @param float $amount
     * @param string $transactionCode
     * @param string $cancelUrl
     * @param string|null $email
     * @param string|null $successUrl
     * @param string|null $sessionId
     * @param string|null $locale
     * @param string|null $notifyUrl
     * @param string|null $selectedCard
     *
     * @return string
     */
    public function generate(
        $gatewayUrl,
        $merchantAlias,
        $secretKey,
        $macMethod,
        $amount,
        $transactionCode,
        $cancelUrl,
        $email = null,
        $successUrl = null,
        $sessionId = null,
        $locale = null,
        $notifyUrl = null,
        $selectedCard = null
    );
}
