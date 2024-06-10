<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Checker
{
    /**
     * @throws InvalidMacException
     */
    public function checkSignature(Signed $signed, string $secretKey, string $macMethod): void;
}
