<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Psr\Log\LoggerInterface;

class UrlGenerator
{
    const SHA1_METHOD = 'sha1';
    const MD5_METHOD = 'md5';
    const EURO_CURRENCY_CODE = 'EUR';

    /**
     * Virtual POS gateway URL it should be https://ecommerce.nexi.it/ecomm/ecomm/DispatcherServlet
     * @var string
     */
    private $gatewayUrl;

    /**
     * Merchant alias (aka "alias")
     * @var string
     */
    private $merchantAlias;

    /**
     * Transaction amount (aka "importo")
     * @var float
     */
    private $amount;

    /**
     * Currency identifier (aka "divisa")
     * @var string
     */
    private $currency;

    /**
     * Transaction identification code (aka "codTrans")
     * @var string
     */
    private $transactionCode;

    /**
     * URL where user will be redirected when he cancel the transaction (aka "url_back")
     * @var string
     */
    private $cancelUrl;

    /**
     * Secret key for MAC calculation
     * @var string
     */
    private $secretKey;

    /**
     * Email address where transaction result will be sent (aka "email")
     * @var string
     */
    private $email;

    /**
     * URL where user will be redirected when the transaction is successful (aka "url")
     * @var string
     */
    private $successUrl;

    /**
     * Session identifier (aka "sess_id")
     * @var string
     */
    private $sessionId;

    /**
     * Language identifier code (aka "languageId")
     * @var string
     */
    private $locale;

    /**
     * Server to server transaction feedback notification URL (aka "urlpost")
     * @var string
     */
    private $notifyUrl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MAC calculation method it should be "sha1" or "md5"
     * @var string
     */
    private $macMethod;

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
     * @param string $currency
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
        $currency,
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

        $this->gatewayUrl = $gatewayUrl;
        $this->merchantAlias = $merchantAlias;
        $this->secretKey = $secretKey;
        $this->macMethod = $macMethod;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->transactionCode = $transactionCode;
        $this->cancelUrl = $cancelUrl;
        $this->email = $email;
        $this->successUrl = $successUrl;
        $this->sessionId = $sessionId;
        $this->locale = $locale;
        $this->notifyUrl = $notifyUrl;

        $this->checkMacMethod();
        $this->checkCurrency();
        $params = $this->mapMandatoryParameters();
        $params = $this->addOptionalParameters($params);
        $params['mac'] = $this->calculateMac($params);
        if ($this->logger) {
            $this->logger->debug(sprintf('Calculated MAC is "%s"', $params['mac']));
        }
        $url = $this->gatewayUrl . '?' . http_build_query($params);
        if ($this->logger) {
            $this->logger->debug(sprintf('Generated URL is "%s"', $url));
        }
        return $url;
    }

    /**
     * Returns the list of the allowed MAC calculation methods.
     * @return array
     */
    public function getAllowedMacCalculationMethods()
    {
        return array(self::SHA1_METHOD => 'SHA1 Hash', self::MD5_METHOD => 'MD5 Hash');
    }

    /**
     * Returns whether the given MAC method should be used with base64 encoding
     * @param $method
     * @return bool
     */
    public static function isBase64EncodeEnabledForMethod($method)
    {
        switch ($method) {
            case self::MD5_METHOD:
                return true;
            case self::SHA1_METHOD:
            default:
                return false;
        }
    }

    private function mapMandatoryParameters()
    {
        return array(
            'alias' => $this->merchantAlias,
            'importo' => round($this->amount, 2) * 100,
            'divisa' => $this->currency,
            'codTrans' => $this->transactionCode,
            'url_back' => $this->cancelUrl,
        );
    }

    private function addOptionalParameters(array $params)
    {
        $optionalMap = array(
            'mail' => $this->email,
            'url' => $this->successUrl,
            'session_id' => $this->sessionId,
            'languageId' => $this->locale,
            'urlpost' => $this->notifyUrl,
        );
        foreach ($optionalMap as $k => $value) {
            if (null === $value) {
                unset($optionalMap[$k]);
            }
        }
        return array_merge($params, $optionalMap);
    }

    private function calculateMac(array $params)
    {
        $macString = '';
        $paramsForMac = array('codTrans', 'divisa', 'importo');
        foreach ($paramsForMac as $param) {
            $macString .= sprintf('%s=%s', $param, $params[$param]);
        }
        $macString .= $this->secretKey;
        $method = $this->macMethod;
        if ($this->logger) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $method));
        }
        if (self::isBase64EncodeEnabledForMethod($method)) {
            return urlencode(base64_encode($method($macString)));
        }
        return $method($macString);
    }

    /**
     * @return array
     */
    private function getAllowedMacCalculationMethodsCodes()
    {
        return array_keys($this->getAllowedMacCalculationMethods());
    }

    private function checkMacMethod()
    {
        if (!in_array($this->macMethod, $this->getAllowedMacCalculationMethodsCodes(), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid MAC calculation method "%s" (only "%s" allowed).',
                    $this->macMethod,
                    implode(', ', $this->getAllowedMacCalculationMethodsCodes())
                )
            );
        }
    }

    private function checkCurrency()
    {
        if ($this->currency !== self::EURO_CURRENCY_CODE) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid currency "%s", the only supported currency is "%s".',
                    $this->macMethod,
                    self::EURO_CURRENCY_CODE
                )
            );
        }
    }
}
