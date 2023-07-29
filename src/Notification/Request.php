<?php

namespace Webgriffe\LibQuiPago\Notification;

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

    /**
     * @var string
     */
    private $mac;

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
        $this->brand = isset($rawParams['$BRAND']) ? $rawParams['$BRAND'] : null;
        $this->mac = urldecode($rawParams['mac']);
        $this->esito = $rawParams['esito'];
        $this->data = $rawParams['data'];
        $this->orario = $rawParams['orario'];

        $this->codAut = isset($rawParams['codAut']) ? $rawParams['codAut'] : null;
        $this->pan = isset($rawParams['Pan']) ? $rawParams['Pan'] : null;
        $this->scadenza_pan = isset($rawParams['Scadenza_pan']) ? $rawParams['Scadenza_pan'] : null;

        $this->nazionalita = isset($rawParams['nazionalita']) ? $rawParams['nazionalita'] : null;

        $this->nome = isset($rawParams['nome']) ? $rawParams['nome'] : null;
        $this->cognome = isset($rawParams['cognome']) ? $rawParams['cognome'] : null;
        $this->mail = isset($rawParams['mail']) ? $rawParams['mail'] : null;
        $this->session_id = isset($rawParams['session_id']) ? $rawParams['session_id'] : null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return static
     */
    public static function buildFromHttpRequest(ServerRequestInterface $request)
    {
        if (strtoupper($request->getMethod()) == 'POST') {
            $rawParams = $request->getParsedBody();
        } else {
            $rawParams = $request->getQueryParams();
        }

        return new static($rawParams);
    }

    /**
     * @param array $rawParams
     * @throws \InvalidArgumentException
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

    /**
     * @return string
     */
    public function getMac()
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

    public function getSignatureFields()
    {
        return [
            'codTrans' => $this->getCodTrans() ?: '',
            'esito' => $this->getEsito() ?: '',
            'importo' => $this->getImporto() ?: '',
            'divisa' => $this->getDivisa() ?: '',
            'data' => $this->getData() ?: '',
            'orario' => $this->getOrario() ?: '',
            'codAut' => $this->getCodAut() ?: '',
        ];
    }

    public function getSignature()
    {
        return $this->getMac();
    }
}
