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
    /**
     * @param Signed $signed
     * @param $secretKey
     * @param $macMethod
     * @return void
     *
     * @throws InvalidMacException
     */
    public function checkSignature(Signed $signed, $secretKey, $macMethod);
}
