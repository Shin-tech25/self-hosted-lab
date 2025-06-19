<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests;

use OCA\Recognize\Vendor\Rubix\ML\Report;
use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use IteratorAggregate;
use JsonSerializable;
use ArrayAccess;
use Stringable;
/**
 * @group Results
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Report
 * @internal
 */
class ReportTest extends TestCase
{
    /**
     * @var Report
     */
    protected $results;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->results = new Report(['accuracy' => 0.9, 'f1_score' => 0.75, 'cardinality' => 5]);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Report::class, $this->results);
        $this->assertInstanceOf(ArrayAccess::class, $this->results);
        $this->assertInstanceOf(JsonSerializable::class, $this->results);
        $this->assertInstanceOf(IteratorAggregate::class, $this->results);
        $this->assertInstanceOf(Stringable::class, $this->results);
    }
    /**
     * @test
     */
    public function toArray() : void
    {
        $expected = ['accuracy' => 0.9, 'f1_score' => 0.75, 'cardinality' => 5];
        $this->assertEquals($expected, $this->results->toArray());
    }
    /**
     * @test
     */
    public function toJSON() : void
    {
        $expected = '{"accuracy":0.9,"f1_score":0.75,"cardinality":5}';
        $encoding = $this->results->toJSON(\false);
        $this->assertInstanceOf(Encoding::class, $encoding);
        $this->assertEquals($expected, (string) $encoding);
    }
    /**
     * @test
     */
    public function arrayAccess() : void
    {
        $this->assertEquals(0.9, $this->results['accuracy']);
        $this->assertEquals(0.75, $this->results['f1_score']);
        $this->assertEquals(5, $this->results['cardinality']);
    }
}
