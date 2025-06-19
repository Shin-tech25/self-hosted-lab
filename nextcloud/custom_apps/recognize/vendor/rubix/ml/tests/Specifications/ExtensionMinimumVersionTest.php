<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @requires extension json
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion
 * @internal
 */
class ExtensionMinimumVersionTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param ExtensionMinimumVersion $specification
     * @param bool $expected
     */
    public function passes(ExtensionMinimumVersion $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [ExtensionMinimumVersion::with('json', '0.0.0'), \true]);
        (yield [ExtensionMinimumVersion::with('json', '999.0.0'), \false]);
        (yield [ExtensionMinimumVersion::with('What about the forest?', '0.0.0'), \false]);
    }
}
