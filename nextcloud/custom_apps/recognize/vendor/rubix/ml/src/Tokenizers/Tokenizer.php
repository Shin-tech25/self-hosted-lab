<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tokenizers;

use Stringable;
/**
 * Tokenizer
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Tokenizer extends Stringable
{
    /**
     * Tokenize a blob of text.
     *
     * @internal
     *
     * @param string $text
     * @return list<string>
     */
    public function tokenize(string $text) : array;
}
