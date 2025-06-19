<?php

namespace OCA\Recognize\Vendor\Wamania\Snowball;

use OCA\Recognize\Vendor\voku\helper\UTF8;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Catalan;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Danish;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Dutch;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\English;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Finnish;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\French;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\German;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Italian;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Norwegian;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Portuguese;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Romanian;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Russian;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Spanish;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Stemmer;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Swedish;
/** @internal */
class StemmerFactory
{
    const LANGS = [Catalan::class => ['ca', 'cat', 'catalan'], Danish::class => ['da', 'dan', 'danish'], Dutch::class => ['nl', 'dut', 'nld', 'dutch'], English::class => ['en', 'eng', 'english'], Finnish::class => ['fi', 'fin', 'finnish'], French::class => ['fr', 'fre', 'fra', 'french'], German::class => ['de', 'deu', 'ger', 'german'], Italian::class => ['it', 'ita', 'italian'], Norwegian::class => ['no', 'nor', 'norwegian'], Portuguese::class => ['pt', 'por', 'portuguese'], Romanian::class => ['ro', 'rum', 'ron', 'romanian'], Russian::class => ['ru', 'rus', 'russian'], Spanish::class => ['es', 'spa', 'spanish'], Swedish::class => ['sv', 'swe', 'swedish']];
    /**
     * @throws NotFoundException
     */
    public static function create(string $code) : Stemmer
    {
        $code = UTF8::strtolower($code);
        foreach (self::LANGS as $classname => $isoCodes) {
            if (\in_array($code, $isoCodes)) {
                return new $classname();
            }
        }
        throw new NotFoundException(\sprintf('Stemmer not found for %s', $code));
    }
}
