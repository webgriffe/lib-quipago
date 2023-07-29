<?php

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class DefaultChecker implements Checker
{
    private \Webgriffe\LibQuiPago\Signature\SignatureHasingManager $signatureHasingManager;

    public function __construct(private ?\Psr\Log\LoggerInterface $logger = null, SignatureHasingManager $signatureHasingManager = null)
    {
        if (!$signatureHasingManager instanceof \Webgriffe\LibQuiPago\Signature\SignatureHasingManager) {
            $signatureHasingManager = new DefaultSignatureHashingManager();
        }

        $this->signatureHasingManager = $signatureHasingManager;
    }

    /**
     * @param $secretKey
     * @param $macMethod
     * @return void
     * @throws InvalidMacException
     */
    public function checkSignature(Signed $signed, $secretKey, $macMethod)
    {
        $macCalculationString = '';
        foreach ($signed->getSignatureFields() as $fieldName => $signatureField) {
            $macCalculationString .= sprintf('%s=%s', $fieldName, $signatureField);
        }

        $macCalculationStringWithSecretKey = $macCalculationString . $secretKey;

        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macCalculationString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $macMethod));
        }

        $calculatedSignature = $this->signatureHasingManager->hashSignatureString(
            $macCalculationStringWithSecretKey,
            $macMethod
        );

        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('Calculated MAC is "%s"', $calculatedSignature));
            $this->logger->debug(sprintf('MAC from request is "%s"', $signed->getSignature()));
        }

        if (function_exists('hash_equals')) {
            $hashEquals = hash_equals($calculatedSignature, $signed->getSignature());
        } else {
            $hashEquals = strcmp($calculatedSignature, $signed->getSignature()) === 0;
        }

        if ($hashEquals) {
            if ($this->logger instanceof \Psr\Log\LoggerInterface) {
                $this->logger->debug('MAC from request is valid');
            }

            return;
        }

        throw new InvalidMacException(
            sprintf(
                'Invalid MAC from notification request. It is "%s", but should be "%s" '.
                '(the %s hash of "%s" plus the secret key).',
                $signed->getSignature(),
                $calculatedSignature,
                $macMethod,
                $macCalculationString
            )
        );
    }
}
