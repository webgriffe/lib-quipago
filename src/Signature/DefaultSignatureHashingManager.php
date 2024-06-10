<?php

namespace Webgriffe\LibQuiPago\Signature;

use Webgriffe\LibQuiPago\Lists\SignatureMethod;

class DefaultSignatureHashingManager implements SignatureHasingManager
{
    public function hashSignatureString(string $string, string $method): string
    {
        $encodedString = match ($method) {
            SignatureMethod::MD5_METHOD => md5($string),
            SignatureMethod::SHA1_METHOD => sha1($string),
            default => throw new \InvalidArgumentException("Unknown hash method {$method} requested"),
        };

        if ($this->mustEncodeHashResultAsUrlencodedBase64($method)) {
            $encodedString = base64_encode($encodedString);
        }

        return $encodedString;
    }

    private function mustEncodeHashResultAsUrlencodedBase64(string $method): bool
    {
        return $method === SignatureMethod::MD5_METHOD;
    }
}
