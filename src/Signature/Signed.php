<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 16/05/18
 * Time: 15.08
 */

namespace Webgriffe\LibQuiPago\Signature;

interface Signed
{
    public function getSignatureFields();

    public function getSignature();
}
