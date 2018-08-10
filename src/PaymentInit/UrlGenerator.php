<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Signature\Signer;

class UrlGenerator
{
    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Signer $signer, LoggerInterface $logger = null)
    {
        $this->signer = $signer;
        $this->logger = $logger;
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
        $notifyUrl = null
    ) {
        if ($this->logger) {
            $this->logger->debug(sprintf('%s method called', __METHOD__));
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
            $notifyUrl
        );

        $this->signer->sign($request, $secretKey, $macMethod);

        $params = $request->getParams();

        if ($this->logger) {
            $this->logger->debug('Request params: '.print_r($params, true));
        }

        $url = $gatewayUrl . '?' . http_build_query($params);

        if ($this->logger) {
            $this->logger->debug(sprintf('Generated URL is "%s"', $url));
        }

        return $url;
    }
}
