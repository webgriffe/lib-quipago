<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 16/05/18
 * Time: 15.08
 */

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class StandardChecker implements Checker
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SignatureHasingManagerInterface
     */
    private $hashingManager;

    public function __construct(LoggerInterface $logger = null, SignatureHasingManagerInterface $hashingManager = null)
    {
        $this->logger = $logger;
        if (!$hashingManager) {
            $hashingManager = new SignatureHashingManager();
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

        $calculatedSignature = $this->hashingManager->hashSignatureString(
            $macCalculationStringWithSecretKey,
            $macMethod
        );

        if (hash_equals($calculatedSignature, $signed->getSignature())) {
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
