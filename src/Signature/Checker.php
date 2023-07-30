<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Checker
{
    /**
     * @throws InvalidMacException
     */
    public function checkSignature(Signed $signed, $secretKey, $macMethod): void;
}
