<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signable
{
    public function getSignatureData(): array;

    public function setSignature(string $signature): static;
}
