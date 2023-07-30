<?php

namespace Webgriffe\LibQuiPago\Notification;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Webgriffe\LibQuiPago\Signature\Signed;

class Request implements Signed
{
    private string $alias;

    private int $importo;

    private string $divisa;

    private string $codTrans;

    private string $data;

    private string $orario;

    private string $mac;

    private ?string $codAut;

    private string $esito;

    private ?string $session_id;

    private ?string $brand;

    private ?string $nome;

    private ?string $cognome;

    private ?string $mail;

    private ?string $nazionalita;

    private ?string $pan;

    private ?string $scadenza_pan;

    private function __construct(array $rawParams)
    {
        $this->checkForMissingParameters($rawParams);
        $this->validateParameters($rawParams);

        $this->alias = (string) $rawParams['alias'];
        $this->importo = (int) $rawParams['importo'];
        $this->divisa = (string) $rawParams['divisa'];
        $this->codTrans = (string) $rawParams['codTrans'];
        $this->brand = $rawParams['$BRAND'] ?? null;
        $this->mac = urldecode($rawParams['mac']);
        $this->esito = (string) $rawParams['esito'];
        $this->data = (string) $rawParams['data'];
        $this->orario = (string) $rawParams['orario'];

        $this->codAut = $rawParams['codAut'] ?? null;
        $this->pan = $rawParams['Pan'] ?? null;
        $this->scadenza_pan = $rawParams['Scadenza_pan'] ?? null;

        $this->nazionalita = $rawParams['nazionalita'] ?? null;

        $this->nome = $rawParams['nome'] ?? null;
        $this->cognome = $rawParams['cognome'] ?? null;
        $this->mail = $rawParams['mail'] ?? null;
        $this->session_id = $rawParams['session_id'] ?? null;
    }

    public static function buildFromHttpRequest(ServerRequestInterface $serverRequest): static
    {
        if (strtoupper($serverRequest->getMethod()) === 'POST') {
            $rawParams = $serverRequest->getParsedBody();
        } else {
            $rawParams = $serverRequest->getQueryParams();
        }

        return new static($rawParams);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function checkForMissingParameters(array $rawParams): void
    {
        $requiredParams = ['alias', 'importo', 'divisa', 'codTrans', 'mac', 'esito', 'data', 'orario'];
        $missingParams = [];
        foreach ($requiredParams as $requiredParam) {
            if (!array_key_exists($requiredParam, $rawParams)) {
                $missingParams[] = $requiredParam;
            }
        }

        if ($missingParams !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid payment notification request. Required parameter(s) missing: "%s"',
                    implode(', ', $missingParams)
                )
            );
        }
    }

    private function validateParameters(array $rawParams): void
    {
        $rawAmount = $rawParams['importo'];
        if (!ctype_digit($rawAmount)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid payment notification request. Amount parameter (importo) should be an integer number, ' .
                    '"%s" given.',
                    $rawAmount
                )
            );
        }
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getImporto(): int
    {
        return $this->importo;
    }

    public function getDivisa(): string
    {
        return $this->divisa;
    }

    public function getCodTrans(): string
    {
        return $this->codTrans;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getOrario(): string
    {
        return $this->orario;
    }

    public function getMac(): string
    {
        return $this->mac;
    }

    public function getCodAut(): ?string
    {
        return $this->codAut;
    }

    public function getEsito(): string
    {
        return $this->esito;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function getCognome(): ?string
    {
        return $this->cognome;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function getNazionalita(): ?string
    {
        return $this->nazionalita;
    }

    public function getPan(): ?string
    {
        return $this->pan;
    }

    public function getScadenzaPan(): ?string
    {
        return $this->scadenza_pan;
    }

    public function getSignatureFields(): array
    {
        return [
            'codTrans' => $this->getCodTrans(),
            'esito' => $this->getEsito(),
            'importo' => $this->getImporto() !== 0 ? $this->getImporto() : '',
            'divisa' => $this->getDivisa(),
            'data' => $this->getData(),
            'orario' => $this->getOrario(),
            'codAut' => $this->getCodAut(),
        ];
    }

    public function getSignature(): string
    {
        return $this->getMac();
    }
}
