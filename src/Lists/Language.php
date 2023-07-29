<?php

namespace Webgriffe\LibQuiPago\Lists;

class Language
{
    const LANGUAGE_ITA = 'ITA';
    const LANGUAGE_ENG = 'ENG';
    const LANGUAGE_SPA = 'SPA';
    const LANGUAGE_FRA = 'FRA';
    const LANGUAGE_GER = 'GER';
    const LANGUAGE_JPG = 'JPG';
    const LANGUAGE_CHI = 'CHI';
    const LANGUAGE_ARA = 'ARA';
    const LANGUAGE_RUS = 'RUS';
    const LANGUAGE_POR = 'POR';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function getList()
    {
        return [
            self::LANGUAGE_ITA => 'Italian',
            self::LANGUAGE_ENG => 'English',
            self::LANGUAGE_SPA => 'Spanish',
            self::LANGUAGE_FRA => 'French',
            self::LANGUAGE_GER => 'German',
            self::LANGUAGE_JPG => 'Japanese',
            self::LANGUAGE_CHI => 'Chinese',
            self::LANGUAGE_ARA => 'Arabic',
            self::LANGUAGE_RUS => 'Russian',
            self::LANGUAGE_POR => 'Portuguese',
        ];
    }
}
