<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 09/05/18
 * Time: 17.29
 */

namespace Webgriffe\LibQuiPago\PaymentInit;

class Request implements \Webgriffe\LibQuiPago\Signature\Signable
{
    /**
     * Merchant alias (aka "alias")
     * @var string
     */
    private $merchantAlias;

    /**
     * Transaction amount (aka "importo")
     * @var float
     */
    private $amount;

    /**
     * Transaction identification code (aka "codTrans")
     * @var string
     */
    private $transactionCode;

    /**
     * URL where user will be redirected when he cancel the transaction (aka "url_back")
     * @var string
     */
    private $cancelUrl;

    /**
     * Email address where transaction result will be sent (aka "email")
     * @var string
     */
    private $email;

    /**
     * URL where user will be redirected when the transaction is successful (aka "url")
     * @var string
     */
    private $successUrl;

    /**
     * Session identifier (aka "sess_id")
     * @var string
     */
    private $sessionId;

    /**
     * Language identifier code (aka "languageId")
     * @var string
     */
    private $locale;

    /**
     * Server to server transaction feedback notification URL (aka "urlpost")
     * @var string
     */
    private $notifyUrl;

    /**
     * Signature string
     * @var string
     */
    private $mac;

    public function __construct(
        $merchantAlias,
        $amount,
        $transactionCode,
        $cancelUrl,
        $email,
        $successUrl,
        $sessionId,
        $locale,
        $notifyUrl
    ) {
        $this->merchantAlias = $merchantAlias;
        $this->amount = $amount;
        $this->transactionCode = $transactionCode;
        $this->cancelUrl = $cancelUrl;
        $this->email = $email;
        $this->successUrl = $successUrl;
        $this->sessionId = $sessionId;
        $this->locale = $locale;
        $this->notifyUrl = $notifyUrl;
    }

    public function getSignatureData()
    {
        $paramValues = $this->getMandatoryParameters();

        $result = array();
        foreach (array('codTrans', 'divisa', 'importo') as $paramName) {
            $result[$paramName] = $paramValues[$paramName];
        }

        return $result;
    }

    public function setSignature($signature)
    {
        $this->mac = $signature;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        //@todo: check thet the request has been signed

        return array_merge(
            $this->getMandatoryParameters(),
            $this->getOptionalParameters(),
            array('mac' => $this->mac)
        );
    }

    private function getMandatoryParameters()
    {
        return array(
            'alias' => $this->merchantAlias,
            'importo' => $this->getAmountAsNumberOfCents(),
            'divisa' => \Webgriffe\LibQuiPago\Lists\Currency::EURO_CURRENCY_CODE,
            'codTrans' => $this->transactionCode,
            'url_back' => $this->cancelUrl,
        );
    }

    private function getOptionalParameters()
    {
        $optionalMap = array(
            'mail' => $this->email,
            'url' => $this->successUrl,
            'session_id' => $this->sessionId,
            'languageId' => $this->locale,
            'urlpost' => $this->notifyUrl,
        );

        foreach ($optionalMap as $k => $value) {
            if (null === $value) {
                unset($optionalMap[$k]);
            }
        }

        return $optionalMap;
    }

    /**
     * @return int
     */
    private function getAmountAsNumberOfCents()
    {
        return round($this->amount * 100);
    }
}