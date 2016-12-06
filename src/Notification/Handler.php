<?php

namespace Webgriffe\LibQuiPago\Notification;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\PaymentInit\UrlGenerator;

class Handler
{
    /**
     * @var string
     */
    private $merchantAlias;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $transactionCode;

    /**
     * @var \DateTime
     */
    private $transactionDate;

    /**
     * @var string
     */
    private $authCode;

    /**
     * @var bool
     */
    private $transactionResult;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $cardBrand;

    /**
     * @var string
     */
    private $macFromRequest;

    /**
     * @var string
     */
    private $cardCountry;

    /**
     * @var string|null
     */
    private $pan;

    /**
     * @var \DateTime|null
     */
    private $panExpiration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $macMethod;

    /**
     * Handler constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Handle notification request
     * @param string $secretKey Secret key for MAC calculation
     * @param string $macMethod MAC calculation method. It should be 'sha1' or 'md5'
     * @param array $rawParams Raw notification POST request params (e.g. $_POST array)
     * @throws InvalidMacException
     */
    public function handle($secretKey, $macMethod, array $rawParams)
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('%s method called', __METHOD__));
            $this->logger->debug(sprintf('Secret key: "%s"', $secretKey));
            $this->logger->debug(sprintf('Request params: %s', json_encode($rawParams)));
        }
        $this->macMethod = $macMethod;
        $this->mapNotificationParams($rawParams);
        $this->validateMac($secretKey, $rawParams);
    }

    /**
     * @return string
     */
    public function getMerchantAlias()
    {
        return $this->merchantAlias;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return string
     */
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    /**
     * @return \DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @return boolean
     */
    public function isTransactionResultPositive()
    {
        return $this->transactionResult;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getCardBrand()
    {
        return $this->cardBrand;
    }

    /**
     * @return string
     */
    public function getMacFromRequest()
    {
        return $this->macFromRequest;
    }

    /**
     * @return string
     */
    public function getCardCountry()
    {
        return $this->cardCountry;
    }

    /**
     * @return string|null
     */
    public function getPan()
    {
        return $this->pan;
    }

    /**
     * @return \DateTime|null
     */
    public function getPanExpiration()
    {
        return $this->panExpiration;
    }

    private function mapNotificationParams(array $rawParams)
    {
        $this->checkForMissingParameters($rawParams);
        $this->validateParameters($rawParams);
        $this->merchantAlias = $rawParams['alias'];
        $this->amount = $rawParams['importo'] / 100;
        $this->currency = $rawParams['divisa'];
        $this->transactionCode = $rawParams['codTrans'];
        $this->transactionDate = new \DateTime($rawParams['data'] . ' ' . $rawParams['orario']);
        $this->macFromRequest = $rawParams['mac'];
        $this->authCode = isset($rawParams['codAut']) ? $rawParams['codAut'] : null;
        $this->transactionResult = $rawParams['esito'] === 'OK' ? true : false;
        $this->sessionId = isset($rawParams['session_id']) ? $rawParams['session_id'] : null;
        $this->cardBrand = isset($rawParams['$BRAND']) ? $rawParams['$BRAND'] : null;
        $this->firstName = isset($rawParams['nome']) ? $rawParams['nome'] : null;
        $this->lastName = isset($rawParams['cognome']) ? $rawParams['cognome'] : null;
        $this->email = isset($rawParams['mail']) ? $rawParams['mail'] : null;
        $this->cardCountry = isset($rawParams['nazionalita']) ? $rawParams['nazionalita'] : null;
        $this->pan = isset($rawParams['Pan']) ? $rawParams['Pan'] : null;
        $this->panExpiration = isset($rawParams['Scadenza_pan']) ? new \DateTime($rawParams['Scadenza_pan']) : null;
    }

    /**
     * @param array $rawParams
     */
    private function checkForMissingParameters(array $rawParams)
    {
        $requiredParams = array(
            'alias',
            'importo',
            'divisa',
            'codTrans',
            'mac',
            'esito',
            'data',
            'orario',
        );
        $missingParams = array();
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $rawParams)) {
                $missingParams[] = $param;
            }
        }
        if (!empty($missingParams)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid payment notification request. Required parameter(s) missing: "%s"',
                    implode(', ', $missingParams)
                )
            );
        }
    }

    private function validateMac($secretKey, array $rawParams)
    {
        $macCalculationParams = array('codTrans', 'esito', 'importo', 'divisa', 'data', 'orario', 'codAut');
        $macCalculationString = '';
        foreach ($macCalculationParams as $macCalculationParam) {
            $macCalculationString .= sprintf(
                '%s=%s',
                $macCalculationParam,
                isset($rawParams[$macCalculationParam]) ? $rawParams[$macCalculationParam] : ''
            );
        }
        $macCalculationString .= $secretKey;
        $macMethod = $this->macMethod;
        if ($this->logger) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macCalculationString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $macMethod));
        }
        $calculatedMac = $macMethod($macCalculationString);
        if (UrlGenerator::isBase64EncodeEnabledForMethod($macMethod)) {
            $calculatedMac = base64_encode($macMethod($macCalculationString));
        }
        if ($calculatedMac === $this->macFromRequest) {
            return;
        }
        throw new InvalidMacException(
            sprintf(
                'Invalid MAC from notification request. It is "%s", but should be "%s" (the %s hash of "%s").',
                $this->macFromRequest,
                $calculatedMac,
                $macMethod,
                $macCalculationString
            )
        );
    }

    private function validateParameters(array $rawParams)
    {
        $rawAmount = $rawParams['importo'];
        if (!ctype_digit($rawAmount)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid payment notification request. Amount parameter (importo) should be an integer number, ' .
                    '"%s" given.',
                    $rawAmount
                )
            );
        }
    }
}
