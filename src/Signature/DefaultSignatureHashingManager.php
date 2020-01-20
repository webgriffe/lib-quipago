<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 16/05/18
 * Time: 15.14
 */

namespace Webgriffe\LibQuiPago\Signature;

use Webgriffe\LibQuiPago\Lists\SignatureMethod;

class DefaultSignatureHashingManager implements SignatureHasingManager
{
    public function hashSignatureString($string, $method)
    {
        switch ($method) {
            case SignatureMethod::MD5_METHOD:
                $encodedString = md5($string);
                break;
            case SignatureMethod::SHA1_METHOD:
                $encodedString = sha1($string);
                break;
            default:
                throw new \InvalidArgumentException("Unknown hash method {$method} requested");
        }

        if ($this->mustEncodeHashResultAsUrlencodedBase64($method)) {
            $encodedString = base64_encode($encodedString);
        }

        return $encodedString;
    }

    private function mustEncodeHashResultAsUrlencodedBase64($method)
    {
        return $method == SignatureMethod::MD5_METHOD;
    }
}
