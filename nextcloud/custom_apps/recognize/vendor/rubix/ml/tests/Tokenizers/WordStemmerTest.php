<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Tokenizers;

use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\Word;
use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\Tokenizer;
use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\WordStemmer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Tokenizers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Tokenizers\WordStemmer
 * @internal
 */
class WordStemmerTest extends TestCase
{
    /**
     * @var WordStemmer
     */
    protected $tokenizer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->tokenizer = new WordStemmer('english');
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(WordStemmer::class, $this->tokenizer);
        $this->assertInstanceOf(Word::class, $this->tokenizer);
        $this->assertInstanceOf(Tokenizer::class, $this->tokenizer);
    }
    /**
     * @test
     * @dataProvider tokenizeProvider
     *
     * @param string $text
     * @param list<string> $expected
     */
    public function tokenize(string $text, array $expected) : void
    {
        $tokens = $this->tokenizer->tokenize($text);
        $this->assertEquals($expected, $tokens);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function tokenizeProvider() : Generator
    {
        /**
         * English
         */
        (yield ["If something's important enough, you should try. Even if - the probable outcome is failure.", ['If', 'someth', 'import', 'enough', 'you', 'should', 'tri', 'even', 'if', '-', 'the', 'probabl', 'outcom', 'is', 'failur']]);
    }
}
