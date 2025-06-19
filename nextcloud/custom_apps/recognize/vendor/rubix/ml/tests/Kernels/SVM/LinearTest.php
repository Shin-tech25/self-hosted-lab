<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Linear;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Kernel;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Kernels
 * @requires extension svm
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Linear
 * @internal
 */
class LinearTest extends TestCase
{
    /**
     * @var Linear
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new Linear();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Linear::class, $this->kernel);
        $this->assertInstanceOf(Kernel::class, $this->kernel);
    }
    /**
     * @test
     */
    public function options() : void
    {
        $expected = [102 => 0];
        $this->assertEquals($expected, $this->kernel->options());
    }
}
