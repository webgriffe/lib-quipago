<?php

namespace Webgriffe\LibQuiPago\Signature;

interface SignatureHasingManager
{
    public function hashSignatureString($string, $method);
}
