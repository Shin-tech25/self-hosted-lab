<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Tokenizers;

use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\NGram;
/**
 * @Groups({"Tokenizers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class NGramBench
{
    protected const SAMPLE_TEXT = "Do you see any Teletubbies in here? Do you see a slender plastic tag clipped to my shirt with my name printed on it? Do you see a little Asian child with a blank expression on his face sitting outside on a mechanical helicopter that shakes when you put quarters in it? No? Well, that's what you see at a toy store. And you must think you're in a toy store, because you're here shopping for an infant named Jeb.";
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Tokenizers\NGram;
     */
    protected $tokenizer;
    public function setUp() : void
    {
        $this->tokenizer = new NGram(1, 2);
    }
    /**
     * @Subject
     * @revs(1000)
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function tokenize() : void
    {
        $this->tokenizer->tokenize(self::SAMPLE_TEXT);
    }
}
