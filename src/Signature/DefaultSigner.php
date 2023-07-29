<?php

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class DefaultSigner implements Signer
{
    private \Webgriffe\LibQuiPago\Signature\SignatureHasingManager $signatureHasingManager;

    public function __construct(private ?\Psr\Log\LoggerInterface $logger = null, SignatureHasingManager $signatureHasingManager = null)
    {
        if (!$signatureHasingManager instanceof \Webgriffe\LibQuiPago\Signature\SignatureHasingManager) {
            $signatureHasingManager = new DefaultSignatureHashingManager();
        }

        $this->signatureHasingManager = $signatureHasingManager;
    }

    public function sign(Signable $signable, $secretKey, $method): void
    {
        $macString = '';
        foreach ($signable->getSignatureData() as $fieldName => $value) {
            $macString .= sprintf('%s=%s', $fieldName, $value);
        }

        $macString .= $secretKey;

        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $method));
        }

        $mac = $this->signatureHasingManager->hashSignatureString($macString, $method);

        if ($this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger->debug(sprintf('Calculated MAC is "%s"', $mac));
        }

        $signable->setSignature(urlencode($mac));
    }
}
