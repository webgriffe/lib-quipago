<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signed
{
    public function getSignatureFields();

    public function getSignature();
}
