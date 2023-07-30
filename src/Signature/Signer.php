<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signer
{
    public function sign(Signable $signable, $secretKey, $method): void;
}
