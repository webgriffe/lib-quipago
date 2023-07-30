<?php

namespace Webgriffe\LibQuiPago\Signature;

use InvalidArgumentException;
use Webgriffe\LibQuiPago\Lists\SignatureMethod;

class DefaultSignatureHashingManager implements SignatureHashingManager
{
    public function hashSignatureString($string, $method): string
    {
        $encodedString = match ($method) {
            SignatureMethod::MD5_METHOD => md5($string),
            SignatureMethod::SHA1_METHOD => sha1($string),
            default => throw new InvalidArgumentException(sprintf('Unknown hash method %s requested', $method)),
        };

        if ($this->mustEncodeHashResultAsUrlencodedBase64($method)) {
            return base64_encode($encodedString);
        }

        return $encodedString;
    }

    private function mustEncodeHashResultAsUrlencodedBase64($method): bool
    {
        return $method === SignatureMethod::MD5_METHOD;
    }
}
