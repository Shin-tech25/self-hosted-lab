<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Extractors;

use OCA\Recognize\Vendor\Rubix\ML\Extractors\CSV;
use OCA\Recognize\Vendor\Rubix\ML\Extractors\Extractor;
use OCA\Recognize\Vendor\Rubix\ML\Extractors\Concatenator;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use IteratorAggregate;
use Traversable;
/**
 * @group Extractors
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Extractors\Concatenator
 * @internal
 */
class ConcatenatorTest extends TestCase
{
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Extractors\Concatenator;
     */
    protected $extractor;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->extractor = new Concatenator([new CSV('tests/test.csv', \true), new CSV('tests/test.csv', \true)]);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Concatenator::class, $this->extractor);
        $this->assertInstanceOf(Extractor::class, $this->extractor);
        $this->assertInstanceOf(IteratorAggregate::class, $this->extractor);
        $this->assertInstanceOf(Traversable::class, $this->extractor);
    }
    /**
     * @test
     */
    public function extract() : void
    {
        $expected = [['attitude' => 'nice', 'texture' => 'furry', 'sociability' => 'friendly', 'rating' => '4', 'class' => 'not monster'], ['attitude' => 'mean', 'texture' => 'furry', 'sociability' => 'loner', 'rating' => '-1.5', 'class' => 'monster'], ['attitude' => 'nice', 'texture' => 'rough', 'sociability' => 'friendly', 'rating' => '2.6', 'class' => 'not monster'], ['attitude' => 'mean', 'texture' => 'rough', 'sociability' => 'friendly', 'rating' => '-1', 'class' => 'monster'], ['attitude' => 'nice', 'texture' => 'rough', 'sociability' => 'friendly', 'rating' => '2.9', 'class' => 'not monster'], ['attitude' => 'nice', 'texture' => 'furry', 'sociability' => 'loner', 'rating' => '-5', 'class' => 'not monster'], ['attitude' => 'nice', 'texture' => 'furry', 'sociability' => 'friendly', 'rating' => '4', 'class' => 'not monster'], ['attitude' => 'mean', 'texture' => 'furry', 'sociability' => 'loner', 'rating' => '-1.5', 'class' => 'monster'], ['attitude' => 'nice', 'texture' => 'rough', 'sociability' => 'friendly', 'rating' => '2.6', 'class' => 'not monster'], ['attitude' => 'mean', 'texture' => 'rough', 'sociability' => 'friendly', 'rating' => '-1', 'class' => 'monster'], ['attitude' => 'nice', 'texture' => 'rough', 'sociability' => 'friendly', 'rating' => '2.9', 'class' => 'not monster'], ['attitude' => 'nice', 'texture' => 'furry', 'sociability' => 'loner', 'rating' => '-5', 'class' => 'not monster']];
        $records = \iterator_to_array($this->extractor, \false);
        $this->assertEquals($expected, $records);
    }
}
