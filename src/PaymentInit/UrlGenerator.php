<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Psr\Log\LoggerInterface;

class UrlGenerator
{
    /**
     * Virtual POS gateway URL it should be https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet
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

    public function __construct($gatewayUrl, $merchantAlias, $secretKey, $macMethod = 'sha1')
    {
        $this->gatewayUrl = $gatewayUrl;
        $this->merchantAlias = $merchantAlias;
        $this->secretKey = $secretKey;
        $this->macMethod = $macMethod;
        if (!in_array($this->macMethod, $this->getAllowedMacCalculationMethods())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid MAC calculation method "%s" (only "%s" allowed).',
                    $this->macMethod,
                    implode(', ', $this->getAllowedMacCalculationMethods())
                )
            );
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function generate(
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

        $this->amount = $amount;
        $this->currency = $currency;
        $this->transactionCode = $transactionCode;
        $this->cancelUrl = $cancelUrl;
        $this->email = $email;
        $this->successUrl = $successUrl;
        $this->sessionId = $sessionId;
        $this->locale = $locale;
        $this->notifyUrl = $notifyUrl;

        $params = $this->mapMandatoryParameters();
        $params = $this->addOptionalParameters($params);
        $params['mac'] = $this->calculateMac($params);
        if ($this->logger) {
            $this->logger->debug(sprintf('Calculated MAC is "%s"', $params['mac']));
        }
        return $this->gatewayUrl . '?' . http_build_query($params);
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
            if (is_null($value)) {
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
        return $method($macString);
    }

    /**
     * @return array
     */
    private function getAllowedMacCalculationMethods()
    {
        return array('sha1', 'md5');
    }
}
