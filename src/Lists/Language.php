<?php
/**
 * Created by PhpStorm.
 * User: atedeschi
 * Date: 16/05/18
 * Time: 16.52
 */
namespace Webgriffe\LibQuiPago\Lists;

class Language
{
    const LANGUAGE_ITA = 'ITA';
    const LANGUAGE_ENG = 'ENG';
    const LANGUAGE_SPA = 'SPA';
    const LANGUAGE_FRA = 'FRA';
    const LANGUAGE_GER = 'GER';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function getList()
    {
        return [
            [self::LANGUAGE_ITA => 'Italian'],
            [self::LANGUAGE_ENG => 'English'],
            [self::LANGUAGE_SPA => 'Spanish'],
            [self::LANGUAGE_FRA => 'French'],
            [self::LANGUAGE_GER => 'German'],
        ];
    }
}