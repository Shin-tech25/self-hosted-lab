<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests;

use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Other
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Encoding
 * @internal
 */
class EncodingTest extends TestCase
{
    protected const TEST_DATA = ['breakfast' => 'pancakes', 'lunch' => 'croque monsieur', 'dinner' => 'new york strip steak'];
    /**
     * @var Encoding
     */
    protected $encoding;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->encoding = new Encoding(\json_encode(self::TEST_DATA) ?: '');
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Encoding::class, $this->encoding);
    }
    /**
     * @test
     */
    public function data() : void
    {
        $expected = '{"breakfast":"pancakes","lunch":"croque monsieur","dinner":"new york strip steak"}';
        $this->assertEquals($expected, $this->encoding->data());
    }
    /**
     * @test
     */
    public function bytes() : void
    {
        $this->assertSame(82, $this->encoding->bytes());
    }
}
