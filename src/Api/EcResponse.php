<?php

namespace Webgriffe\LibQuiPago\Api;

use Psr\Http\Message\ResponseInterface;

class EcResponse
{
    const POSITIVE_RESULT_CODE = '0';

    /**
     * @var string
     */
    private $rawBody;
    /**
     * @var int
     */
    private $resultCode;
    /**
     * @var string
     */
    private $merchantAlias;
    /**
     * @var string
     */
    private $transactionCode;
    /**
     * @var string
     */
    private $operationId;
    /**
     * @var string
     */
    private $operationType;
    /**
     * @var string
     */
    private $operationAmount;
    /**
     * @var string
     */
    private $mac;

    /**
     * EcResponse constructor.
     * @param string $rawBody
     * @param string $macKey
     */
    private function __construct($rawBody, $macKey)
    {
        try {
            $xmlReader = new \SimpleXMLElement($rawBody);
        } catch (\Exception $e) {
            throw new ValidationException(
                sprintf('The string "%s" is an invalid EcResponse body. %s', $rawBody, $e->getMessage())
            );
        }
        $this->resultCode = (string)$xmlReader->ECRES->esitoRichiesta;
        $this->merchantAlias = (string)$xmlReader->alias;
        $this->transactionCode = (string)$xmlReader->ECRES->codTrans;
        $this->operationId = (string)$xmlReader->ECRES->id_op;
        $this->operationType = (string)$xmlReader->ECRES->type_op;
        $this->operationAmount = (string)$xmlReader->ECRES->importo_op;
        $this->mac = $this->validateMac((string)$xmlReader->mac, $macKey);
        $this->rawBody = $rawBody;
    }

    /**
     * @param ResponseInterface $response
     * @param string $macKey
     * @return EcResponse
     */
    public static function createFromPsrResponse(ResponseInterface $response, $macKey)
    {
        return new self($response->getBody(), $macKey);
    }

    /**
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * @return bool
     */
    public function isPositive()
    {
        return $this->resultCode === self::POSITIVE_RESULT_CODE;
    }

    /**
     * @return string|null
     */
    public function getErrorMessageByResultCode()
    {
        switch ($this->resultCode) {
            case self::POSITIVE_RESULT_CODE:
                return null;
            case '1':
                return 'Errore nella richiesta: Formato del messaggio errato o campo mancante o errato';
            case '3':
                return 'Errore nella richiesta: Campo id_op duplicato (caso "FA") o non trovato (caso "RA")';
            case '16':
                return 'Errore nella richiesta: Campo alias sconosciuto o non abilitato';
            case '18':
                return 'Errore nella richiesta: operazione negata dall’emittente della carta di credito';
            case '2':
                return 'Errore nella richiesta: Errore imprevisto durante l’elaborazione della richiesta';
            case '8':
                return 'Errore nella richiesta: mac errato';
            case '21':
                return 'Errore nella richiesta: Campo codTrans sconosciuto';
            case '22':
                return 'Errore nella richiesta: operazione non eseguibile (es. storno superiore all’incasso)';
            default:
                return sprintf(
                    'Unknown result code "%s". Please refer to updated documentation to find related error message.',
                    $this->resultCode
                );
        }
    }

    private function validateMac($mac, $macKey)
    {
        $macString = implode(
            '',
            array(
                $this->merchantAlias,
                $this->transactionCode,
                $this->operationId,
                $this->operationType,
                $this->operationAmount,
                $macKey
            )
        );
        $expectedMac = sha1($macString);
        if (strtolower($expectedMac) === strtolower($mac)) {
            return $mac;
        }
        throw new ValidationException(
            sprintf(
                'Invalid MAC code in EcResponse body. Expected MAC was "%s", "%s" given.',
                strtolower($expectedMac),
                strtolower($mac)
            )
        );
    }
}
