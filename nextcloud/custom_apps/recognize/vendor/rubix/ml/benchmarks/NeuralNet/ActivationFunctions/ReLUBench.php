<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\NeuralNet\ActivationFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
/**
 * @Groups({"ActivationFunctions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class ReLUBench
{
    /**
     * @var Matrix
     */
    protected $z;
    /**
     * @var Matrix
     */
    protected $computed;
    /**
     * @var ReLU
     */
    protected $activationFn;
    public function setUp() : void
    {
        $this->z = Matrix::uniform(500, 500);
        $this->computed = Matrix::uniform(500, 500);
        $this->activationFn = new ReLU();
    }
    /**
     * @Subject
     * @Iterations(3)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function activate() : void
    {
        $this->activationFn->activate($this->z);
    }
    /**
     * @Subject
     * @Iterations(3)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function differentiate() : void
    {
        $this->activationFn->differentiate($this->z, $this->computed);
    }
}
