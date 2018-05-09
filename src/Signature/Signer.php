<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 09/05/18
 * Time: 17.44
 */

namespace Webgriffe\LibQuiPago\Signature;

use Psr\Log\LoggerInterface;
use Webgriffe\LibQuiPago\Lists\SignatureMethod;

class Signer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

        $signable->setSignature($this->computeHash($macString, $method));
    }

    private function computeHash($macString, $method)
    {
        switch ($method) {
            case SignatureMethod::MD5_METHOD:
                $encodedString = md5($macString);
                break;
            case SignatureMethod::SHA1_METHOD:
                $encodedString = sha1($macString);
                break;
            default:
                throw new \InvalidArgumentException("Unknown hash method {$method} requested");
        }

        if ($this->mustEcodeHashResultAsUrlencodedBase64($method)) {
            $encodedString = urlencode(base64_encode($encodedString));
        }

        if ($this->logger) {
            $this->logger->debug("Computed mac string is: ".$encodedString);
        }

        return $encodedString;
    }

    private function mustEcodeHashResultAsUrlencodedBase64($method)
    {
        return $method == SignatureMethod::MD5_METHOD;
    }
}
