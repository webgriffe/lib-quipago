<?php
/**
 * Created by PhpStorm.
 * User: kraken
 * Date: 10/08/18
 * Time: 15.17
 */

namespace Webgriffe\LibQuiPago\Signature;

interface Checker
{
    public function checkSignature(Signed $signed, $secretKey, $macMethod);
}