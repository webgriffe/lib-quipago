<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 16/05/18
 * Time: 15.17
 */

namespace Webgriffe\LibQuiPago\Signature;


interface SignatureHasingManagerInterface
{
    public function hashSignatureString($string, $method);
}
