<?php

namespace Webgriffe\LibQuiPago\Signature;

interface Signable
{
    public function getSignatureData();

    public function setSignature($signature);
}
