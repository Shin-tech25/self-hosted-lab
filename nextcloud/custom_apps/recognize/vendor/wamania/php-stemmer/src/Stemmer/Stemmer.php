<?php

namespace OCA\Recognize\Vendor\Wamania\Snowball\Stemmer;

/**
 * @author Luís Cobucci <lcobucci@gmail.com>
 * @internal
 */
interface Stemmer
{
    /**
     * Main function to get the STEM of a word
     *
     * @param string $word A valid UTF-8 word
     *
     * @return string
     *
     * @throws \Exception
     */
    public function stem($word);
}
