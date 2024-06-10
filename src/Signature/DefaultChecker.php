<?php

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class DefaultChecker implements Checker
{
    private ?LoggerInterface $logger;

    private SignatureHasingManager $hashingManager;

    public function __construct(LoggerInterface $logger = null, SignatureHasingManager $hashingManager = null)
    {
        $this->logger = $logger;
        if (!$hashingManager) {
            $hashingManager = new DefaultSignatureHashingManager();
        }
        $this->hashingManager = $hashingManager;
    }

    public function checkSignature(Signed $signed, string $secretKey, string $macMethod): void
    {
        $macCalculationString = '';
        foreach ($signed->getSignatureFields() as $fieldName => $value) {
            $macCalculationString .= sprintf('%s=%s', $fieldName, $value);
        }
        $macCalculationStringWithSecretKey = $macCalculationString . $secretKey;

        $this->logger?->debug(sprintf('MAC calculation string is "%s"', $macCalculationString));
        $this->logger?->debug(sprintf('MAC calculation method is "%s"', $macMethod));

        $calculatedSignature = $this->hashingManager->hashSignatureString(
            $macCalculationStringWithSecretKey,
            $macMethod
        );

        $this->logger?->debug("Calculated MAC is \"{$calculatedSignature}\"");
        $this->logger?->debug("MAC from request is \"{$signed->getSignature()}\"");

        $hashEquals = hash_equals($calculatedSignature, $signed->getSignature());

        if ($hashEquals) {
            $this->logger?->debug('MAC from request is valid');
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
