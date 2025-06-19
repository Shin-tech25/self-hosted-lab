<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\RBF;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Kernel;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Kernels
 * @requires extension svm
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\RBF
 * @internal
 */
class RBFTest extends TestCase
{
    /**
     * @var RBF
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new RBF(0.001);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(RBF::class, $this->kernel);
        $this->assertInstanceOf(Kernel::class, $this->kernel);
    }
    /**
     * @test
     */
    public function options() : void
    {
        $options = [102 => 2, 201 => 0.001];
        $this->assertEquals($options, $this->kernel->options());
    }
}
