<?php

namespace OCA\Recognize\Vendor\OkBloomer\Tests;

use OCA\Recognize\Vendor\OkBloomer\BloomFilter;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Base
 * @covers \OkBloomer\BloomFilter
 * @internal
 */
class BloomFilterTest extends TestCase
{
    /**
     * @var \OkBloomer\BloomFilter
     */
    protected $filter;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->filter = new BloomFilter(0.001, 3, 1024);
    }
    /**
     * @test
     */
    public function existsOrInsert() : void
    {
        $this->assertFalse($this->filter->existsOrInsert('foo'));
        $this->assertFalse($this->filter->existsOrInsert('bar'));
        $this->assertTrue($this->filter->exists('foo'));
    }
    /**
     * @test
     */
    public function insert() : void
    {
        $this->assertFalse($this->filter->exists('foo'));
        $this->filter->insert('foo');
        $this->assertTrue($this->filter->exists('foo'));
    }
}
