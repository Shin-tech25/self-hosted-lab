<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty
 * @internal
 */
class SpecificationChainTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param SpecificationChain $specification
     * @param bool $expected
     */
    public function passes(SpecificationChain $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        $dataset = Unlabeled::quick([['swamp', 'island', 'black knight', 'counter spell']]);
        (yield [SpecificationChain::with([new DatasetIsNotEmpty($dataset)]), \true]);
        (yield [SpecificationChain::with([new DatasetIsNotEmpty($dataset), new DatasetIsLabeled($dataset)]), \false]);
    }
}
