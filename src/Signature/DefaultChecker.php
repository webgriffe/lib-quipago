<?php

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class DefaultChecker implements Checker
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SignatureHasingManager
     */
    private $hashingManager;

    public function __construct(LoggerInterface $logger = null, SignatureHasingManager $hashingManager = null)
    {
        $this->logger = $logger;
        if (!$hashingManager) {
            $hashingManager = new DefaultSignatureHashingManager();
        }
        $this->hashingManager = $hashingManager;
    }

    /**
     * @param Signed $signed
     * @param $secretKey
     * @param $macMethod
     * @return void
     *
     * @throws InvalidMacException
     */
    public function checkSignature(Signed $signed, $secretKey, $macMethod)
    {
        $macCalculationString = '';
        foreach ($signed->getSignatureFields() as $fieldName => $value) {
            $macCalculationString .= sprintf('%s=%s', $fieldName, $value);
        }
        $macCalculationStringWithSecretKey = $macCalculationString . $secretKey;

        if ($this->logger) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macCalculationString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $macMethod));
        }

        $calculatedSignature = $this->hashingManager->hashSignatureString(
            $macCalculationStringWithSecretKey,
            $macMethod
        );

        if ($this->logger) {
            $this->logger->debug("Calculated MAC is \"{$calculatedSignature}\"");
            $this->logger->debug("MAC from request is \"{$signed->getSignature()}\"");
        }

        if (function_exists('hash_equals')) {
            $hashEquals = hash_equals($calculatedSignature, $signed->getSignature());
        } else {
            $hashEquals = strcmp($calculatedSignature, $signed->getSignature()) === 0;
        }

        if ($hashEquals) {
            if ($this->logger) {
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
