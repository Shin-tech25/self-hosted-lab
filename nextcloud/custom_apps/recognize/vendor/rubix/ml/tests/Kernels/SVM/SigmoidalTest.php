<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Sigmoidal;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Kernel;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Kernels
 * @requires extension svm
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Sigmoidal
 * @internal
 */
class SigmoidalTest extends TestCase
{
    /**
     * @var Sigmoidal
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new Sigmoidal(0.001);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Sigmoidal::class, $this->kernel);
        $this->assertInstanceOf(Kernel::class, $this->kernel);
    }
    /**
     * @test
     */
    public function options() : void
    {
        $options = [102 => 3, 201 => 0.001, 205 => 0.0];
        $this->assertEquals($options, $this->kernel->options());
    }
}
