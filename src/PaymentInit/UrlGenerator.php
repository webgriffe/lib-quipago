<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Lists\SignatureMethod;
use Webgriffe\LibQuiPago\Signature\Signer;

class UrlGenerator
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
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

        $signer = new Signer($this->logger);
        $signer->sign($request, $secretKey, $macMethod);

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

    /**
     * Returns whether the given MAC method should be used with base64 encoding
     * @param $method
     * @return bool
     */
    public static function isBase64EncodeEnabledForMethod($method)
    {
        switch ($method) {
            case SignatureMethod::MD5_METHOD:
                return true;
            case SignatureMethod::SHA1_METHOD:
            default:
                return false;
        }
    }
}
