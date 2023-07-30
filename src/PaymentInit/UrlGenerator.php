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
    public const VISA_SELECTEDCARD         = 'VISA';

    public const MASTERCARD_SELECTEDCARD   = 'MasterCard';

    public const AMEX_SELECTEDCARD         = 'Amex';

    public const DINERS_SELECTEDCARD       = 'Diners';

    public const JCB_SELECTEDCARD          = 'Jcb';

    public const MAESTRO_SELECTEDCARD      = 'Maestro';

    //...and more generic payment methods
    public const MYBANK_SELECTEDCARD       = 'SCT';

    public const CREDIT_CARD_SELECTEDCARD  = 'CC';

             //Any credit card
    public const MASTERPASS_SELECTEDCARD   = 'Masterpass';

    public const SOFORT_SELECTEDCARD       = 'SOFORT';

    public const PAYPAL_SELECTEDCARD       = 'PAYPAL';

    public const AMAZONPAY_SELECTEDCARD    = 'AMAZONPAY';

    public const GOOGLEPAY_SELECTEDCARD    = 'GOOGLEPAY';

    public const APPLEPAY_SELECTEDCARD     = 'APPLEPAY';

    public const ALIPAY_SELECTEDCARD       = 'ALIPAY';

    public const WECHATPAY_SELECTEDCARD    = 'WECHATPAY';

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
