<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Checker
{
    /**
     * @param Signed $signed
     * @param $secretKey
     * @param $macMethod
     * @return void
     *
     * @throws InvalidMacException
     */
    public function checkSignature(Signed $signed, $secretKey, $macMethod);
}
