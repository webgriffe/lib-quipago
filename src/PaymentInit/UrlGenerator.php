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

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

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
        return $this->gatewayUrl . '?' . http_build_query($params);
    }

    /**
     * Returns the list of allowed currencies
     * @return array
     */
    public function getAllowedCurrencies()
    {
        return array(
            'EUR' => 'Euro',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'HKD' => 'Hong Kong Dollar',
            'JPY' => 'Japan Yen',
            'CHF' => 'Swiss Franc',
            'GBP' => 'Pound Sterling',
            'USD' => 'US Dollar',
            'BRL' => 'Brazil real (1994-)',
            'SGD' => 'Singapore dollar',
            'AED' => 'United Arab Emirates dirham',
            'TWD' => 'Taiwan new dollar',
            'SAR' => 'Saudi Arabia riyal',
            'IDR' => 'Indonesia rupiah',
            'THB' => 'Thailand baht',
            'KWD' => 'Kuwait dinar',
            'MYR' => 'Malaysia ringgit',
            'QAR' => 'Qatar riyal',
            'MXN' => 'Mexico peso',
            'ZAR' => 'South Africa rand',
            'KRW' => 'Korea, South won',
            'PLN' => 'Polish Zloty',
            'INR' => 'India rupee',
            'PHP' => 'Philippines peso',
            'CZK' => 'Czech Republic koruna',
            'NZD' => 'New Zealand dollar',
            'CLP' => 'Chile peso',
            'RON' => 'Romanian New Leu',
            'HUF' => 'Hungary forint',
            'COP' => 'Colombia peso',
            'BHD' => 'Bahrain dinar',
            'EGP' => 'Egypt pound',
            'HRK' => 'Croatia kuna',
            'LVL' => 'Latvia lat',
            'VEF' => 'Venezuelan Bolivar Fuerte',
            'JOD' => 'Jordan dinar',
            'ARS' => 'Argentina peso (1991-)',
            'MOP' => 'Macao (Macau) pataca',
            'DKK' => 'Corona Danese',
        );
    }

    /**
     * Returns the list of the allowed MAC calculation methods.
     * @return array
     */
    public function getAllowedMacCalculationMethods()
    {
        return array('sha1' => 'SHA1 Hash', 'md5' => 'MD5 Hash');
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
    private function getAllowedMacCalculationMethodsCodes()
    {
        return array_keys($this->getAllowedMacCalculationMethods());
    }

    private function checkMacMethod()
    {
        if (!in_array($this->macMethod, $this->getAllowedMacCalculationMethodsCodes())) {
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
        if (!in_array($this->currency, $this->getAllowedCurrenciesCodes())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid currency "%s" (check documentation to find allowed currencies).',
                    $this->macMethod
                )
            );
        }
    }

    private function getAllowedCurrenciesCodes()
    {
        return array_keys($this->getAllowedCurrencies());
    }
}
