<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\Signer;
use Webgriffe\LibQuiPago\Signature\DefaultSigner;

class DefaultUrlGenerator implements UrlGenerator
{
    private \Webgriffe\LibQuiPago\Signature\Signer $signer;

    public function __construct(private ?\Psr\Log\LoggerInterface $logger = null, Signer $signer = null)
    {
        if (!$signer instanceof \Webgriffe\LibQuiPago\Signature\Signer) {
            $signer = new DefaultSigner($logger);
        }

        $this->signer = $signer;
    }

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
    ) {
        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('%s method called', __METHOD__));
        }

        if ($selectedCard &&
            $selectedCard != self::VISA_SELECTEDCARD &&
            $selectedCard != self::MASTERCARD_SELECTEDCARD &&
            $selectedCard != self::AMEX_SELECTEDCARD &&
            $selectedCard != self::DINERS_SELECTEDCARD &&
            $selectedCard != self::JCB_SELECTEDCARD &&
            $selectedCard != self::MAESTRO_SELECTEDCARD &&
            $selectedCard != self::MYBANK_SELECTEDCARD &&
            $selectedCard != self::CREDIT_CARD_SELECTEDCARD &&
            $selectedCard != self::MASTERPASS_SELECTEDCARD &&
            $selectedCard != self::SOFORT_SELECTEDCARD &&
            $selectedCard != self::PAYPAL_SELECTEDCARD &&
            $selectedCard != self::AMAZONPAY_SELECTEDCARD &&
            $selectedCard != self::GOOGLEPAY_SELECTEDCARD &&
            $selectedCard != self::APPLEPAY_SELECTEDCARD &&
            $selectedCard != self::ALIPAY_SELECTEDCARD &&
            $selectedCard != self::WECHATPAY_SELECTEDCARD
        ) {
            throw new \RuntimeException(sprintf('Selectedcard value \'%s\' is not one of the allowed values', $selectedCard));
        }

        $request = new Request(
            $merchantAlias,
            $amount,
            $transactionCode,
            $cancelUrl,
            $email,
            $successUrl,
            $sessionId,
            $locale,
            $notifyUrl,
            $selectedCard
        );

        $this->signer->sign($request, $secretKey, $macMethod);

        $params = $request->getParams();

        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug('Request params: '.print_r($params, true));
        }

        $url = $gatewayUrl . '?' . http_build_query($params);

        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('Generated URL is "%s"', $url));
        }

        return $url;
    }
}
