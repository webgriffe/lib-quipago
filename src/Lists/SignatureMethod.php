<?php

namespace Webgriffe\LibQuiPago\Lists;

class SignatureMethod
{
    public const SHA1_METHOD   = 'sha1';

    public const MD5_METHOD    = 'md5';

    public function getList(): array
    {
        return [
            self::SHA1_METHOD => self::SHA1_METHOD,
            self::MD5_METHOD => self::MD5_METHOD
        ];
    }
}
