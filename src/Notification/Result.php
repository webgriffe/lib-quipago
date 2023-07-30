<?php

namespace Webgriffe\LibQuiPago\Notification;

use DateTime;
use Exception;

class Result
{
    /**
     * @deprecated will be removed in 3.0 use Result::OUTCOME_OK instead
     */
    public const POSITIVE_OUTCOME = 'OK';

    public const OUTCOME_OK = 'OK';

    public const OUTCOME_ANNULLO = 'ANNULLO';

    public const OUTCOME_KO = 'KO';

    public const OUTCOME_ERRORE = 'ERRORE';

    private string $merchantAlias;

    private float $amount;

    private string $currency;

    private string $transactionCode;

    private DateTime $dateTime;

    private ?string $authCode;

    private string $outcome;

    private ?string $sessionId;

    private ?string $cardBrand;

    private ?string $firstName;

    private ?string $lastName;

    private ?string $email;

    private ?string $country;

    private ?string $pan;

    private ?string $panExpiration;

    /**
     * @throws Exception
     */
    public function __construct(Request $request)
    {
        $this->merchantAlias = $request->getAlias();
        $this->amount = $request->getImporto() / 100.0;
        $this->currency = $request->getDivisa();
        $this->transactionCode = $request->getCodTrans();
        $this->dateTime = new DateTime($request->getData(). ' ' .$request->getOrario());
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

    public function getMerchantAlias(): string
    {
        return $this->merchantAlias;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTransactionCode(): string
    {
        return $this->transactionCode;
    }

    public function getDate(): DateTime
    {
        return $this->dateTime;
    }

    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    public function getOutcome(): string
    {
        return $this->outcome;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getCardBrand(): ?string
    {
        return $this->cardBrand;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getPan(): ?string
    {
        return $this->pan;
    }

    public function getPanExpiration(): ?string
    {
        return $this->panExpiration;
    }

    public function isTransactionResultPositive(): bool
    {
        return $this->getOutcome() === self::OUTCOME_OK;
    }
}
