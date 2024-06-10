<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signable
{
    /**
     * @return array<string, string|int>
     */
    public function getSignatureData(): array;

    public function setSignature(string $signature): static;
}
