<?php

namespace Webgriffe\LibQuiPago\Api;

use Psr\Http\Message\ResponseInterface;

class EcResponse
{
    public const POSITIVE_RESULT_CODE = '0';

    private string $resultCode;

    private string $merchantAlias;

    private string $transactionCode;

    private string $requestType;

    private string $operationId;

    private string $operationType;

    private string $operationAmount;

    private string $mac;

    private function __construct(private string $rawBody, string $macKey)
    {
        try {
            $xmlReader = new \SimpleXMLElement($rawBody);
        } catch (\Exception $e) {
            throw new ValidationException(
                sprintf('The string "%s" is an invalid EcResponse body. %s', $rawBody, $e->getMessage())
            );
        }

        $this->resultCode = (string) $xmlReader->ECRES->esitoRichiesta;
        $this->merchantAlias = (string) $xmlReader->alias;
        $this->transactionCode = (string) $xmlReader->ECRES->codTrans;
        $this->requestType = (string) $xmlReader->ECRES->request_type;
        $this->operationId = (string) $xmlReader->ECRES->id_op;
        $this->operationType = (string) $xmlReader->ECRES->type_op;
        $this->operationAmount = (string) $xmlReader->ECRES->importo_op;
        $this->mac = $this->validateMac((string) $xmlReader->mac, $macKey);
    }

    public static function createFromPsrResponse(ResponseInterface $response, string $macKey): EcResponse
    {
        return new self($response->getBody()->getContents(), $macKey);
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    public function isPositive(): bool
    {
        return $this->resultCode === self::POSITIVE_RESULT_CODE;
    }

    public function getErrorMessageByResultCode(): ?string
    {
        return match ($this->resultCode) {
            self::POSITIVE_RESULT_CODE => null,
            '1' => 'Errore nella richiesta: Formato del messaggio errato o campo mancante o errato',
            '3' => 'Errore nella richiesta: Campo id_op duplicato (caso "FA") o non trovato (caso "RA")',
            '16' => 'Errore nella richiesta: Campo alias sconosciuto o non abilitato',
            '18' => 'Errore nella richiesta: operazione negata dall’emittente della carta di credito',
            '2' => 'Errore nella richiesta: Errore imprevisto durante l’elaborazione della richiesta',
            '8' => 'Errore nella richiesta: mac errato',
            '21' => 'Errore nella richiesta: Campo codTrans sconosciuto',
            '22' => 'Errore nella richiesta: operazione non eseguibile (es. storno superiore all’incasso)',
            default => sprintf(
                'Unknown result code "%s". Please refer to updated documentation to find related error message.',
                $this->resultCode
            ),
        };
    }

    public function getMac(): string
    {
        return $this->mac;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    public function getMerchantAlias(): string
    {
        return $this->merchantAlias;
    }

    public function getTransactionCode(): string
    {
        return $this->transactionCode;
    }

    public function getRequestType(): string
    {
        return $this->requestType;
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getOperationAmountRaw(): string
    {
        return $this->operationAmount;
    }

    public function getOperationAmount(): float|int
    {
        return (int) $this->operationAmount / 100;
    }

    private function validateMac(string $mac, string $macKey): string
    {
        if ($mac === '') {
            return $mac;
        }
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
                'Invalid MAC code in EcResponse body. Expected MAC was "%s", "%s" given. Raw body is "%s".',
                strtolower($expectedMac),
                strtolower($mac),
                $this->rawBody
            )
        );
    }
}
