<?php

namespace Webgriffe\LibQuiPago\Notification;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Webgriffe\LibQuiPago\Signature\Signed;

class Request implements Signed
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var int
     */
    private $importo;

    /**
     * @var string
     */
    private $divisa;

    /**
     * @var string
     */
    private $codTrans;

    /**
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $orario;

    private string $mac;

    /**
     * @var string
     */
    private $codAut;

    /**
     * @var string
     */
    private $esito;

    /**
     * @var string
     */
    private $session_id;

    /**
     * @var string
     */
    private $brand;

    /**
     * @var string
     */
    private $nome;

    /**
     * @var string
     */
    private $cognome;

    /**
     * @var string
     */
    private $mail;

    /**
     * @var string
     */
    private $nazionalita;

    /**
     * @var string
     */
    private $pan;

    /**
     * @var string
     */
    private $scadenza_pan;

    private function __construct(array $rawParams)
    {
        $this->checkForMissingParameters($rawParams);
        $this->validateParameters($rawParams);

        $this->alias = $rawParams['alias'];
        $this->importo = $rawParams['importo'];
        $this->divisa = $rawParams['divisa'];
        $this->codTrans = $rawParams['codTrans'];
        $this->brand = $rawParams['$BRAND'] ?? null;
        $this->mac = urldecode($rawParams['mac']);
        $this->esito = $rawParams['esito'];
        $this->data = $rawParams['data'];
        $this->orario = $rawParams['orario'];

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
    private function checkForMissingParameters(array $rawParams)
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

    private function validateParameters(array $rawParams)
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

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return int
     */
    public function getImporto()
    {
        return $this->importo;
    }

    /**
     * @return string
     */
    public function getDivisa()
    {
        return $this->divisa;
    }

    /**
     * @return string
     */
    public function getCodTrans()
    {
        return $this->codTrans;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getOrario()
    {
        return $this->orario;
    }

    public function getMac(): string
    {
        return $this->mac;
    }

    /**
     * @return string
     */
    public function getCodAut()
    {
        return $this->codAut;
    }

    /**
     * @return string
     */
    public function getEsito()
    {
        return $this->esito;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @return string
     */
    public function getCognome()
    {
        return $this->cognome;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return string
     */
    public function getNazionalita()
    {
        return $this->nazionalita;
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
    public function getScadenzaPan()
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
