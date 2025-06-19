<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Elastic;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Reversible;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TfIdfTransformer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\TfIdfTransformer
 * @internal
 */
class TfIdfTransformerTest extends TestCase
{
    /**
     * @var TfIdfTransformer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new TfIdfTransformer(1.0, \false);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(TfIdfTransformer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
        $this->assertInstanceOf(Elastic::class, $this->transformer);
        $this->assertInstanceOf(Reversible::class, $this->transformer);
        $this->assertInstanceOf(Persistable::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransformReverse() : void
    {
        $dataset = new Unlabeled([[1, 3, 0, 0, 1, 0, 0, 0, 1, 2, 0, 2, 0, 0, 0, 4, 1, 0, 1], [0, 1, 1, 0, 0, 2, 1, 0, 0, 0, 0, 3, 0, 1, 0, 0, 0, 0, 0], [0, 0, 0, 1, 2, 3, 0, 0, 4, 2, 0, 0, 1, 0, 2, 0, 1, 0, 0]]);
        $this->transformer->fit($dataset);
        $this->assertTrue($this->transformer->fitted());
        $dfs = $this->transformer->dfs();
        $this->assertIsArray($dfs);
        $this->assertCount(19, $dfs);
        $this->assertContainsOnly('int', $dfs);
        $original = clone $dataset;
        $dataset->apply($this->transformer);
        $expected = [[1.6931471805599454, 3.8630462173553424, 0.0, 0.0, 1.2876820724517808, 0.0, 0.0, 0.0, 1.2876820724517808, 2.5753641449035616, 0.0, 2.5753641449035616, 0.0, 0.0, 0.0, 6.772588722239782, 1.2876820724517808, 0.0, 1.6931471805599454], [0.0, 1.2876820724517808, 1.6931471805599454, 0.0, 0.0, 2.5753641449035616, 1.6931471805599454, 0.0, 0.0, 0.0, 0.0, 3.8630462173553424, 0.0, 1.6931471805599454, 0.0, 0.0, 0.0, 0.0, 0.0], [0.0, 0.0, 0.0, 1.6931471805599454, 2.5753641449035616, 3.8630462173553424, 0.0, 0.0, 5.150728289807123, 2.5753641449035616, 0.0, 0.0, 1.6931471805599454, 0.0, 3.386294361119891, 0.0, 1.2876820724517808, 0.0, 0.0]];
        $this->assertEquals($expected, $dataset->samples());
        $dataset->reverseApply($this->transformer);
        $this->assertEquals($original->samples(), $dataset->samples());
    }
}
