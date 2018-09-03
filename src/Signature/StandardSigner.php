<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 09/05/18
 * Time: 17.44
 */

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;

class StandardSigner implements Signer
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

    public function sign(Signable $signable, $secretKey, $method)
    {
        $macString = '';
        foreach ($signable->getSignatureData() as $fieldName => $value) {
            $macString .= sprintf('%s=%s', $fieldName, $value);
        }

        $macString .= $secretKey;

        if ($this->logger) {
            $this->logger->debug(sprintf('MAC calculation string is "%s"', $macString));
            $this->logger->debug(sprintf('MAC calculation method is "%s"', $method));
        }

        $mac = $this->hashingManager->hashSignatureString($macString, $method);

        if ($this->logger) {
            $this->logger->debug("Calculated MAC is \"{$mac}\"");
        }

        $signable->setSignature(urlencode($mac));
    }
}
