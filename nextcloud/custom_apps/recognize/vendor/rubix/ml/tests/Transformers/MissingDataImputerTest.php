<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Mean;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\KMostFrequent;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\MissingDataImputer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\MissingDataImputer
 * @internal
 */
class MissingDataImputerTest extends TestCase
{
    /**
     * @var MissingDataImputer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new MissingDataImputer(new Mean(), new KMostFrequent(), '?');
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(MissingDataImputer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $dataset = new Unlabeled([[30, 'friendly'], [\NAN, 'mean'], [50, 'friendly'], [60, '?'], [10, 'mean']]);
        $this->transformer->fit($dataset);
        $this->assertTrue($this->transformer->fitted());
        $dataset->apply($this->transformer);
        $this->assertThat($dataset[1][0], $this->logicalAnd($this->greaterThan(20), $this->lessThan(55)));
        $this->assertContains($dataset[3][1], ['friendly', 'mean']);
    }
}
