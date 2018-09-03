<?php

/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 09/05/18
 * Time: 17.38
 */

namespace Webgriffe\LibQuiPago\Signature;

interface Signable
{
    public function getSignatureData();

    public function setSignature($signature);
}
