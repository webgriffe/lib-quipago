<?php

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class DefaultSigner implements Signer
{
    private SignatureHashingManager $signatureHashingManager;

    public function __construct(
        private ?LoggerInterface $logger = null,
        SignatureHashingManager $signatureHashingManager = null
    ) {
        if (!$signatureHashingManager instanceof SignatureHashingManager) {
            $signatureHashingManager = new DefaultSignatureHashingManager();
        }

        $this->signatureHashingManager = $signatureHashingManager;
    }

    public function sign(Signable $signable, $secretKey, $method): void
    {
        $macString = '';
        foreach ($signable->getSignatureData() as $fieldName => $value) {
            $macString .= sprintf('%s=%s', $fieldName, $value);
        }

        $macString .= $secretKey;

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $method));
        }

        $mac = $this->signatureHashingManager->hashSignatureString($macString, $method);

        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug(sprintf('Calculated MAC is "%s"', $mac));
        }

        $signable->setSignature(urlencode($mac));
    }
}
