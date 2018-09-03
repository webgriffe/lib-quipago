<?php
/**
 * Created by PhpStorm.
 * User: kraken
 * Date: 10/08/18
 * Time: 15.17
 */

namespace Webgriffe\LibQuiPago\Signature;

interface Signer
{
    public function sign(Signable $signable, $secretKey, $method);
}
