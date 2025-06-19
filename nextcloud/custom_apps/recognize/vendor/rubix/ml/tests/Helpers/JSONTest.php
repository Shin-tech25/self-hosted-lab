<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Helpers;

use OCA\Recognize\Vendor\Rubix\ML\Helpers\JSON;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Helpers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Helpers\JSON
 * @internal
 */
class JSONTest extends TestCase
{
    /**
     * @test
     */
    public function decode() : void
    {
        $actual = JSON::decode('{"attitude":"nice","texture":"furry","sociability":"friendly","rating":4,"class":"not monster"}');
        $expected = ['attitude' => 'nice', 'texture' => 'furry', 'sociability' => 'friendly', 'rating' => 4, 'class' => 'not monster'];
        $this->assertSame($expected, $actual);
    }
    /**
     * @test
     */
    public function encode() : void
    {
        $actual = JSON::encode(['package' => 'rubix/ml']);
        $expected = '{"package":"rubix\\/ml"}';
        $this->assertSame($expected, $actual);
    }
    /**
     * @test
     */
    public function decodeBadData() : void
    {
        $this->expectException(RuntimeException::class);
        JSON::decode('[{"package":...}]');
    }
}
