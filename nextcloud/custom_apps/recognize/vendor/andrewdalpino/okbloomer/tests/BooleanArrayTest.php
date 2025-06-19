<?php

namespace OCA\Recognize\Vendor\OkBloomer\Tests;

use OCA\Recognize\Vendor\OkBloomer\BooleanArray;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Base
 * @covers \OkBloomer\BooleanArray
 * @internal
 */
class BooleanArrayTest extends TestCase
{
    /**
     * @var \OkBloomer\BooleanArray
     */
    protected $bitmap;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->bitmap = new BooleanArray(1024);
    }
    /**
     * @test
     */
    public function offsetSet() : void
    {
        $this->assertFalse($this->bitmap[42]);
        $this->bitmap[42] = \true;
        $this->assertTrue($this->bitmap[42]);
    }
}
