<?php

namespace Webgriffe\LibQuiPago\Notification;

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
     * Handle notification request
     * @param string $secretKey Secret key for MAC calculation
     * @param array $rawParams Raw notification POST request params (e.g. $_POST array)
     */
    public function handle($secretKey, array $rawParams)
    {
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
        $this->merchantAlias = $rawParams['alias'];
        $this->amount = $rawParams['importo'];
        $this->currency = $rawParams['divisa'];
        $this->sessionId = $rawParams['session_id'];
        $this->transactionCode = $rawParams['codTrans'];
        $this->transactionDate = new \DateTime($rawParams['data'] . ' ' . $rawParams['orario']);
        $this->authCode = $rawParams['codAut'];
        $this->transactionResult = $rawParams['esito'] === 'OK' ? true : false;
        $this->cardBrand = $rawParams['$BRAND'];
        $this->firstName = $rawParams['nome'];
        $this->lastName = $rawParams['cognome'];
        $this->email = $rawParams['email'];
        $this->macFromRequest = $rawParams['mac'];
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
            'session_id',
            'codTrans',
            'data',
            'orario',
            'esito',
            'codAut',
            '$BRAND',
            'nome',
            'cognome',
            'email',
            'mac',
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
            $macCalculationString .= sprintf('%s=%s', $macCalculationParam, $rawParams[$macCalculationParam]);
        }
        $macCalculationString .= $secretKey;
        $calculatedMac = sha1($macCalculationString);
        if ($calculatedMac === $this->macFromRequest) {
            return;
        }
        throw new InvalidMacException(
            sprintf(
                'Invalid MAC from notification request. It is "%s", but should be "%s" (the SHA1 hash of "%s").',
                $this->macFromRequest,
                $calculatedMac,
                $macCalculationString
            )
        );
    }
}
