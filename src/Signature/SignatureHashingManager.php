<?php

namespace Webgriffe\LibQuiPago\Signature;

interface SignatureHashingManager
{
    public function hashSignatureString($string, $method): string;
}
