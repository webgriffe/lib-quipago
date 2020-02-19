<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

interface UrlGenerator
{
    /**
     * Allowed values for the selectedcard field.
     * https://ecommerce.nexi.it/specifiche-tecniche/codicebase/avviopagamento.html
     * https://ecommerce.nexi.it/specifiche-tecniche/tabelleecodifiche/codificatipocarta.html
     * Please note that the MYBANK value is not in this list because it's only allowed in the "brand" field, not in the
     * "selectedcard" one. Use SCT instead.
     */
    //Specific credit card brands:
    const VISA_SELECTEDCARD         = 'VISA';
    const MASTERCARD_SELECTEDCARD   = 'MasterCard';
    const AMEX_SELECTEDCARD         = 'Amex';
    const DINERS_SELECTEDCARD       = 'Diners';
    const JCB_SELECTEDCARD          = 'Jcb';
    const MAESTRO_SELECTEDCARD      = 'Maestro';

    //...and more generic payment methods
    const MYBANK_SELECTEDCARD       = 'SCT';
    const CREDIT_CARD_SELECTEDCARD  = 'CC';         //Any credit card
    const MASTERPASS_SELECTEDCARD   = 'Masterpass';
    const SOFORT_SELECTEDCARD       = 'SOFORT';
    const PAYPAL_SELECTEDCARD       = 'PAYPAL';
    const AMAZONPAY_SELECTEDCARD    = 'AMAZONPAY';
    const GOOGLEPAY_SELECTEDCARD    = 'GOOGLEPAY';
    const APPLEPAY_SELECTEDCARD     = 'APPLEPAY';
    const ALIPAY_SELECTEDCARD       = 'ALIPAY';
    const WECHATPAY_SELECTEDCARD    = 'WECHATPAY';

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
