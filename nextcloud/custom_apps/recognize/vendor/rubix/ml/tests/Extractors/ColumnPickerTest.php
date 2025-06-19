<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Extractors;

use OCA\Recognize\Vendor\Rubix\ML\Extractors\CSV;
use OCA\Recognize\Vendor\Rubix\ML\Extractors\Extractor;
use OCA\Recognize\Vendor\Rubix\ML\Extractors\ColumnPicker;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use IteratorAggregate;
use Traversable;
/**
 * @group Extractors
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Extractors\ColumnPicker
 * @internal
 */
class ColumnPickerTest extends TestCase
{
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Extractors\ColumnPicker;
     */
    protected $extractor;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->extractor = new ColumnPicker(new CSV('tests/test.csv', \true), ['attitude', 'texture', 'class', 'rating']);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(ColumnPicker::class, $this->extractor);
        $this->assertInstanceOf(Extractor::class, $this->extractor);
        $this->assertInstanceOf(IteratorAggregate::class, $this->extractor);
        $this->assertInstanceOf(Traversable::class, $this->extractor);
    }
    /**
     * @test
     */
    public function extract() : void
    {
        $expected = [['attitude' => 'nice', 'texture' => 'furry', 'class' => 'not monster', 'rating' => '4'], ['attitude' => 'mean', 'texture' => 'furry', 'class' => 'monster', 'rating' => '-1.5'], ['attitude' => 'nice', 'texture' => 'rough', 'class' => 'not monster', 'rating' => '2.6'], ['attitude' => 'mean', 'texture' => 'rough', 'class' => 'monster', 'rating' => '-1'], ['attitude' => 'nice', 'texture' => 'rough', 'class' => 'not monster', 'rating' => '2.9'], ['attitude' => 'nice', 'texture' => 'furry', 'class' => 'not monster', 'rating' => '-5']];
        $records = \iterator_to_array($this->extractor, \false);
        $this->assertEquals($expected, $records);
    }
}
