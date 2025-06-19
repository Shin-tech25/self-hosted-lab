<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Tokenizers;

use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\NGram;
use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\Tokenizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Tokenizers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Tokenizers\NGram
 * @internal
 */
class NGramTest extends TestCase
{
    /**
     * @var NGram
     */
    protected $tokenizer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->tokenizer = new NGram(1, 2);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(NGram::class, $this->tokenizer);
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
        (yield ["I'd like to die on Mars, just not on impact. The end.", ["I'd", "I'd like", 'like', 'like to', 'to', 'to die', 'die', 'die on', 'on', 'on Mars', 'Mars', 'Mars just', 'just', 'just not', 'not', 'not on', 'on', 'on impact', 'impact', 'The', 'The end', 'end']]);
    }
}
