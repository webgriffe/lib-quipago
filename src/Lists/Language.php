<?php

namespace Webgriffe\LibQuiPago\Lists;

class Language
{
    public const LANGUAGE_ITA = 'ITA';
    public const LANGUAGE_ENG = 'ENG';
    public const LANGUAGE_SPA = 'SPA';
    public const LANGUAGE_FRA = 'FRA';
    public const LANGUAGE_GER = 'GER';
    public const LANGUAGE_JPG = 'JPG';
    public const LANGUAGE_CHI = 'CHI';
    public const LANGUAGE_ARA = 'ARA';
    public const LANGUAGE_RUS = 'RUS';
    public const LANGUAGE_POR = 'POR';

    /**
     * Get options in "key-value" format
     *
     * @return array<string, string>
     */
    public function getList(): array
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
