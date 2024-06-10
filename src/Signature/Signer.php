<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signer
{
    public function sign(Signable $signable, string $secretKey, string $method): void;
}
