<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signed
{
    public function getSignatureFields(): array;

    public function getSignature(): string;
}
