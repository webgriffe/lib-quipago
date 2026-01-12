<?php

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class DefaultSigner implements Signer
{
    private ?LoggerInterface $logger;

    private SignatureHasingManager $hashingManager;

    public function __construct(?LoggerInterface $logger = null, ?SignatureHasingManager $hashingManager = null)
    {
        $this->logger = $logger;
        if ($hashingManager === null) {
            $hashingManager = new DefaultSignatureHashingManager();
        }
        $this->hashingManager = $hashingManager;
    }

    public function sign(Signable $signable, string $secretKey, string $method): void
    {
        $macString = '';
        foreach ($signable->getSignatureData() as $fieldName => $value) {
            $macString .= sprintf('%s=%s', $fieldName, $value);
        }

        $macString .= $secretKey;

        $this->logger?->debug(sprintf('MAC calculation string is "%s"', $macString));
        $this->logger?->debug(sprintf('MAC calculation method is "%s"', $method));

        $mac = $this->hashingManager->hashSignatureString($macString, $method);

        $this->logger?->debug("Calculated MAC is \"{$mac}\"");

        $signable->setSignature(urlencode($mac));
    }
}
