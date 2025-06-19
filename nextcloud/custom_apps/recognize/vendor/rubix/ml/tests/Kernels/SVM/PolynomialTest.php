<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Polynomial;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Kernel;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Kernels
 * @requires extension svm
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Polynomial
 * @internal
 */
class PolynomialTest extends TestCase
{
    /**
     * @var Polynomial
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new Polynomial(3, 0.001);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Polynomial::class, $this->kernel);
        $this->assertInstanceOf(Kernel::class, $this->kernel);
    }
    /**
     * @test
     */
    public function options() : void
    {
        $expected = [102 => 1, 201 => 0.001, 103 => 3, 205 => 0.0];
        $this->assertEquals($expected, $this->kernel->options());
    }
}
