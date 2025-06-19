<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @requires extension json
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded
 * @internal
 */
class ExtensionIsLoadedTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param ExtensionIsLoaded $specification
     * @param bool $expected
     */
    public function passes(ExtensionIsLoaded $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [ExtensionIsLoaded::with('json'), \true]);
        (yield [ExtensionIsLoaded::with("I be trappin' where I go"), \false]);
    }
}
