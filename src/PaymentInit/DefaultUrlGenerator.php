<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\Signer;
use Webgriffe\LibQuiPago\Signature\DefaultSigner;

class DefaultUrlGenerator implements UrlGenerator
{
    private Signer $signer;

    private ?LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null, Signer $signer = null)
    {
        $this->logger = $logger;
        if (!$signer) {
            $signer = new DefaultSigner($logger);
        }
        $this->signer = $signer;
    }

    public function generate(
        string $gatewayUrl,
        string $merchantAlias,
        string $secretKey,
        string $macMethod,
        float $amount,
        string $transactionCode,
        string $cancelUrl,
        ?string $email = null,
        ?string $successUrl = null,
        ?string $sessionId = null,
        ?string $locale = null,
        ?string $notifyUrl = null,
        ?string $selectedCard = null,
    ): string {
        $this->logger?->debug(sprintf('%s method called', __METHOD__));

        if ($selectedCard !== null &&
            $selectedCard !== self::VISA_SELECTEDCARD &&
            $selectedCard !== self::MASTERCARD_SELECTEDCARD &&
            $selectedCard !== self::AMEX_SELECTEDCARD &&
            $selectedCard !== self::DINERS_SELECTEDCARD &&
            $selectedCard !== self::JCB_SELECTEDCARD &&
            $selectedCard !== self::MAESTRO_SELECTEDCARD &&
            $selectedCard !== self::MYBANK_SELECTEDCARD &&
            $selectedCard !== self::CREDIT_CARD_SELECTEDCARD &&
            $selectedCard !== self::MASTERPASS_SELECTEDCARD &&
            $selectedCard !== self::SOFORT_SELECTEDCARD &&
            $selectedCard !== self::PAYPAL_SELECTEDCARD &&
            $selectedCard !== self::AMAZONPAY_SELECTEDCARD &&
            $selectedCard !== self::GOOGLEPAY_SELECTEDCARD &&
            $selectedCard !== self::APPLEPAY_SELECTEDCARD &&
            $selectedCard !== self::ALIPAY_SELECTEDCARD &&
            $selectedCard !== self::WECHATPAY_SELECTEDCARD
        ) {
            throw new \RuntimeException("Selected card value '{$selectedCard}' is not one of the allowed values");
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

        $this->logger?->debug('Request params: ' . print_r($params, true));

        $url = $gatewayUrl . '?' . http_build_query($params);

        $this->logger?->debug(sprintf('Generated URL is "%s"', $url));

        return $url;
    }
}
