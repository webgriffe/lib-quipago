<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use Webgriffe\LibQuiPago\Lists\Currency;
use Webgriffe\LibQuiPago\Signature\Signable;

class Request implements Signable
{
    public const OPERATION_TYPE_CAPTURE = 'C';
    public const OPERATION_TYPE_AUTHORIZE = 'D';

    /**
     * Signature string
     */
    private ?string $mac = null;

    /**
     * @param string $merchantAlias Merchant alias (aka "alias")
     * @param float $amount Transaction amount (aka "importo")
     * @param string $transactionCode Transaction identification code (aka "codTrans")
     * @param string $cancelUrl URL where user will be redirected when he cancel the transaction (aka "url_back")
     * @param string|null $email Email address where transaction result will be sent (aka "email")
     * @param string|null $successUrl URL where user will be redirected when the transaction is successful (aka "url")
     * @param string|null $sessionId Session identifier (aka "sess_id")
     * @param string|null $locale Language identifier code (aka "languageId")
     * @param string|null $notifyUrl Server to server transaction feedback notification URL (aka "urlpost")
     * @param string|null $selectedcard Preselected payment method to use
     * @param string|null $operationType Operation type (aka "TCONTAB")
     * @param string|null $description Payment description (aka "descrizione")
     */
    public function __construct(
        private string $merchantAlias,
        private float $amount,
        private string $transactionCode,
        private string $cancelUrl,
        private ?string $email,
        private ?string $successUrl,
        private ?string $sessionId,
        private ?string $locale,
        private ?string $notifyUrl,
        private ?string $selectedcard = null,
        private ?string $operationType = null,
        private ?string $description = null
    ) {
    }

    public function getSignatureData(): array
    {
        $paramValues = $this->getMandatoryParameters();

        $result = [];
        foreach (['codTrans', 'divisa', 'importo'] as $paramName) {
            $result[$paramName] = $paramValues[$paramName];
        }

        return $result;
    }

    public function setSignature(string $signature): static
    {
        $this->mac = $signature;

        return $this;
    }

    /**
     * @return array<string, string|int>
     */
    public function getParams(): array
    {
        if (!$this->mac) {
            throw new \RuntimeException(
                'Cannot generate request params without a signature. '.
                'Please sign this object before calling this method'
            );
        }

        return array_merge(
            $this->getMandatoryParameters(),
            $this->getOptionalParameters(),
            ['mac' => $this->mac],
        );
    }

    /**
     * @return array<string, string|int>
     */
    private function getMandatoryParameters(): array
    {
        return [
            'alias'     => $this->merchantAlias,
            'importo'   => $this->getAmountAsNumberOfCents(),
            'divisa'    => Currency::EURO_CURRENCY_CODE,
            'codTrans'  => $this->transactionCode,
            'url'       => $this->successUrl,
            'url_back'  => $this->cancelUrl,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getOptionalParameters(): array
    {
        $optionalMap = [
            'urlpost'       => $this->notifyUrl,
            'mail'          => $this->email,
            'languageId'    => $this->locale,
            'session_id'    => $this->sessionId,
            'selectedcard'  => $this->selectedcard,
            'TCONTAB'       => $this->operationType,
            'descrizione'   => $this->description
        ];

        foreach ($optionalMap as $k => $value) {
            if (null === $value) {
                unset($optionalMap[$k]);
            }
        }

        return $optionalMap;
    }

    private function getAmountAsNumberOfCents(): int
    {
        if (round($this->amount, 2) !== $this->amount) {
            throw new \RuntimeException(
                "Payment amount {$this->amount} cannot be represented as a whole number of cents. ".
                "Maybe there are more than two decimal digits?"
            );
        }

        return (int) round($this->amount * 100);
    }
}
