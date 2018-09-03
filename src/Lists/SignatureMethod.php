<?php
/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 09/05/18
 * Time: 17.54
 */

namespace Webgriffe\LibQuiPago\Lists;

class SignatureMethod
{
    const SHA1_METHOD   = 'sha1';
    const MD5_METHOD    = 'md5';

    /**
     * @return array
     */
    public function getList()
    {
        return [
            self::SHA1_METHOD => self::SHA1_METHOD,
            self::MD5_METHOD => self::MD5_METHOD
        ];
    }
}
