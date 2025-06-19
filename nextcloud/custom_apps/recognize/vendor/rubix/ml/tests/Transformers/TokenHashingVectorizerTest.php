<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Tokenizers\Word;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TokenHashingVectorizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\TokenHashingVectorizer
 * @internal
 */
class TokenHashingVectorizerTest extends TestCase
{
    /**
     * @var TokenHashingVectorizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new TokenHashingVectorizer(20, new Word(), 'crc32');
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(TokenHashingVectorizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = Unlabeled::quick([['the quick brown fox jumped over the lazy man sitting at a bus stop drinking a can of coke'], ['with a dandy umbrella']]);
        $dataset->apply($this->transformer);
        $expected = [[0, 1, 1, 0, 1, 1, 0, 4, 0, 1, 2, 1, 0, 0, 1, 1, 3, 0, 2, 0], [0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1]];
        $this->assertEquals($expected, $dataset->samples());
    }
}
