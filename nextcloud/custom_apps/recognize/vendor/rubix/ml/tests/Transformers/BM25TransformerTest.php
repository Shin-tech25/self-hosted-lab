<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Elastic;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\BM25Transformer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\BM25Transformer
 * @internal
 */
class BM25TransformerTest extends TestCase
{
    /**
     * @var BM25Transformer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new BM25Transformer(1.2, 0.75);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(BM25Transformer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
        $this->assertInstanceOf(Elastic::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $dataset = new Unlabeled([[1, 3, 0, 0, 1, 0, 0, 0, 1, 2, 0, 2, 0, 0, 0, 4, 1, 0, 1], [0, 1, 1, 0, 0, 2, 1, 0, 0, 0, 0, 3, 0, 1, 0, 0, 0, 0, 0], [0, 0, 0, 1, 2, 3, 0, 0, 4, 2, 0, 0, 1, 0, 2, 0, 1, 0, 0]]);
        $this->transformer->fit($dataset);
        $this->assertTrue($this->transformer->fitted());
        $dfs = $this->transformer->dfs();
        $this->assertIsArray($dfs);
        $this->assertCount(19, $dfs);
        $this->assertContainsOnly('int', $dfs);
        $dataset->apply($this->transformer);
        $expected = [[0.2562582002070131, 0.22742881339794754, 0.0, 0.0, 0.13186359514416618, 0.0, 0.0, 0.0, 0.13186359514416618, 0.19254341937443092, 0.0, 0.19254341937443092, 0.0, 0.0, 0.0, 0.4860031535349766, 0.13186359514416618, 0.0, 0.2562582002070131], [0.0, 0.17063795450977862, 0.3316106698128093, 0.0, 0.0, 0.23083934808978732, 0.3316106698128093, 0.0, 0.0, 0.0, 0.0, 0.26160416281731713, 0.0, 0.3316106698128093, 0.0, 0.0, 0.0, 0.0, 0.0], [0.0, 0.0, 0.0, 0.2562582002070131, 0.19254341937443092, 0.22742881339794754, 0.0, 0.0, 0.25008418471976107, 0.19254341937443092, 0.0, 0.0, 0.2562582002070131, 0.0, 0.3741808347986538, 0.0, 0.13186359514416618, 0.0, 0.0]];
        $this->assertEqualsWithDelta($expected, $dataset->samples(), 1.0E-8);
    }
}
