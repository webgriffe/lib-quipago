<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 16/05/18
 * Time: 15.08
 */

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Notification\InvalidMacException;

class SignatureChecker
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SignatureHasingManagerInterface
     */
    private $hashingManager;

    public function __construct(LoggerInterface $logger, SignatureHasingManagerInterface $hashingManager)
    {
        $this->logger = $logger;
        $this->hashingManager = $hashingManager;
    }

    public function checkSignature(Signed $signed, $secretKey, $macMethod)
    {
        $macCalculationString = '';
        foreach ($signed->getSignatureFields() as $fieldName => $value) {
            $macCalculationString .= sprintf('%s=%s', $fieldName, $value);
        }
        $macCalculationString .= $secretKey;

        $calculatedSignature = $this->hashingManager->hashSignatureString($macCalculationString, $macMethod);

        if (hash_equals($calculatedSignature, $signed->getSignature())) {
            if ($this->logger) {
                $this->logger->debug('MAC from request is valid');
            }
            return true;
        }

        throw new InvalidMacException(
            sprintf(
                'Invalid MAC from notification request. It is "%s", but should be "%s" (the %s hash of "%s").',
                $signed->getSignature(),
                $calculatedSignature,
                $macMethod,
                $macCalculationString
            )
        );
    }
}
