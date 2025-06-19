<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\MultibyteTextNormalizer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\MultibyteTextNormalizer
 * @internal
 */
class MultibyteTextNormalizerTest extends TestCase
{
    /**
     * @var Unlabeled
     */
    protected $dataset;
    /**
     * @var MultibyteTextNormalizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new MultibyteTextNormalizer(\false);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(MultibyteTextNormalizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = Unlabeled::quick([['The quick brown fox jumped over the lazy man sitting at a bus' . ' stop drinking a can of Coke'], ['with a Dandy   umbrella'], ['Depuis qu’il avait emménagé à côté de chez elle, il y a de ça cinq ans.'], ['Working with emoji 🤓']]);
        $dataset->apply($this->transformer);
        $expected = [['the quick brown fox jumped over the lazy man sitting at a bus' . ' stop drinking a can of coke'], ['with a dandy   umbrella'], ['depuis qu’il avait emménagé à côté de chez elle, il y a de ça cinq ans.'], ['working with emoji 🤓']];
        $this->assertEquals($expected, $dataset->samples());
    }
}
