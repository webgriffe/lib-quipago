<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signed
{
    /**
     * @return array<string, string|int>
     */
    public function getSignatureFields(): array;

    public function getSignature(): string;
}
