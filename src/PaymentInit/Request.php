<?php

namespace Webgriffe\LibQuiPago\PaymentInit;

use RuntimeException;
use Webgriffe\LibQuiPago\Lists\Currency;
use Webgriffe\LibQuiPago\Signature\Signable;

class Request implements Signable
{
    public const OPERATION_TYPE_CAPTURE = 'C';

    public const OPERATION_TYPE_AUTHORIZE = 'D';

    private string $mac;

    public function __construct(
        /**
         * Merchant alias (aka "alias")
         */
        private string $merchantAlias,
        /**
         * Transaction amount (aka "importo")
         */
        private float $amount,
        /**
         * Transaction identification code (aka "codTrans")
         */
        private string $transactionCode,
        /**
         * URL where user will be redirected when he cancel the transaction (aka "url_back")
         */
        private string $cancelUrl,
        /**
         * Email address where transaction result will be sent (aka "email")
         */
        private string $email,
        /**
         * URL where user will be redirected when the transaction is successful (aka "url")
         */
        private string $successUrl,
        /**
         * Session identifier (aka "sess_id")
         */
        private string $sessionId,
        /**
         * Language identifier code (aka "languageId")
         */
        private string $locale,
        /**
         * Server to server transaction feedback notification URL (aka "urlpost")
         */
        private string $notifyUrl,
        /**
         * Preselected payment method to use
         */
        private ?string $selectedcard = null,
        /**
         * Operation type (aka "TCONTAB")
         */
        private ?string $operationType = null,
        /**
         * Payment description (aka "descrizione")
         */
        private ?string $description = null
    ) {
    }

    public function getSignatureData()
    {
        $paramValues = $this->getMandatoryParameters();

        $result = [];
        foreach (['codTrans', 'divisa', 'importo'] as $paramName) {
            $result[$paramName] = $paramValues[$paramName];
        }

        return $result;
    }

    public function setSignature($signature)
    {
        $this->mac = $signature;
        return $this;
    }

    public function getParams(): array
    {
        if ($this->mac === '' || $this->mac === '0') {
            throw new RuntimeException(
                'Cannot generate request params without a signature. ' .
                'Please sign this object before calling this method'
            );
        }

        return array_merge(
            $this->getMandatoryParameters(),
            $this->getOptionalParameters(),
            ['mac' => $this->mac]
        );
    }

    private function getMandatoryParameters(): array
    {
        return [
            'alias' => $this->merchantAlias,
            'importo' => $this->getAmountAsNumberOfCents(),
            'divisa' => Currency::EURO_CURRENCY_CODE,
            'codTrans' => $this->transactionCode,
            'url' => $this->successUrl,
            'url_back' => $this->cancelUrl
        ];
    }

    private function getOptionalParameters(): array
    {
        $optionalMap = [
            'urlpost' => $this->notifyUrl,
            'mail' => $this->email,
            'languageId' => $this->locale,
            'session_id' => $this->sessionId,
            'selectedcard' => $this->selectedcard,
            'TCONTAB' => $this->operationType,
            'descrizione' => $this->description
        ];

        foreach ($optionalMap as $k => $value) {
            if (null === $value) {
                unset($optionalMap[$k]);
            }
        }

        return $optionalMap;
    }

    private function getAmountAsNumberOfCents(): float
    {
        if (round($this->amount, 2) !== $this->amount) {
            throw new RuntimeException(
                sprintf('Payment amount %s cannot be represented as a whole number of cents. ', $this->amount) .
                "Maybe there are more than two decimal digits?"
            );
        }

        return round($this->amount * 100);
    }
}
