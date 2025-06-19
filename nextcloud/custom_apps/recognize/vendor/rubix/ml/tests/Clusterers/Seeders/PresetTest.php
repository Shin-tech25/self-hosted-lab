<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Clusterers\Seeders;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders\Seeder;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders\Preset;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Seeders
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders\Preset
 * @internal
 */
class PresetTest extends TestCase
{
    /**
     * @var Preset
     */
    protected $seeder;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->seeder = new Preset([['foo', 14, 0.72], ['bar', 16, 0.92], ['beer', 21, 1.26]]);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Preset::class, $this->seeder);
        $this->assertInstanceOf(Seeder::class, $this->seeder);
    }
    /**
     * @test
     */
    public function seed() : void
    {
        $expected = [['foo', 14, 0.72], ['bar', 16, 0.92], ['beer', 21, 1.26]];
        $seeds = $this->seeder->seed(Unlabeled::quick([['beef', 4, 13.0]]), 3);
        $this->assertCount(3, $seeds);
        $this->assertEquals($expected, $seeds);
    }
}
