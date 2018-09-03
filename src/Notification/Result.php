<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 16/05/18
 * Time: 15.55
 */

namespace Webgriffe\LibQuiPago\Notification;

class Result
{
    const POSITIVE_OUTCOME = 'OK';

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
    private $transactionCode;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $authCode;

    /**
     * @var string
     */
    private $outcome;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $cardBrand;

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
    private $country;

    /**
     * @var string
     */
    private $pan;

    /**
     * @var string
     */
    private $panExpiration;

    public function __construct(Request $request)
    {
        $this->merchantAlias = $request->getAlias();
        $this->amount = $request->getImporto() / 100;
        $this->currency = $request->getDivisa();
        $this->transactionCode = $request->getCodTrans();
        $this->date = new \DateTime($request->getData(). ' ' .$request->getOrario());
        $this->authCode = $request->getCodAut();
        $this->outcome = $request->getEsito();
        $this->sessionId = $request->getSessionId();
        $this->cardBrand = $request->getBrand();
        $this->firstName = $request->getNome();
        $this->lastName = $request->getCognome();
        $this->email = $request->getMail();
        $this->country = $request->getNazionalita();
        $this->pan = $request->getPan();
        $this->panExpiration = $request->getScadenzaPan();
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
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @return string
     */
    public function getOutcome()
    {
        return $this->outcome;
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
    public function getCardBrand()
    {
        return $this->cardBrand;
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
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getPan()
    {
        return $this->pan;
    }

    /**
     * @return string
     */
    public function getPanExpiration()
    {
        return $this->panExpiration;
    }

    /**
     * @return bool
     */
    public function isTransactionResultPositive()
    {
        return $this->getOutcome() === self::POSITIVE_OUTCOME;
    }
}
