<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Kernels\Distance;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\SparseCosine;
/**
 * @Groups({"DistanceKernels"})
 * @internal
 */
class SparseCosineBench
{
    protected const NUM_SAMPLES = 10000;
    /**
     * @var list<list<float>>
     */
    protected $aSamples;
    /**
     * @var list<list<float>>
     */
    protected $bSamples;
    /**
     * @var SparseCosine
     */
    protected $kernel;
    public function setUp() : void
    {
        $this->kernel = new SparseCosine();
    }
    public function setUpDense() : void
    {
        $this->aSamples = Matrix::gaussian(self::NUM_SAMPLES, 8)->asArray();
        $this->bSamples = Matrix::gaussian(self::NUM_SAMPLES, 8)->asArray();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @BeforeMethods({"setUp", "setUpDense"})
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function computeDense() : void
    {
        \array_map([$this->kernel, 'compute'], $this->aSamples, $this->bSamples);
    }
    public function setUpSparse() : void
    {
        $mask = Matrix::rand(self::NUM_SAMPLES, 8)->greater(0.5);
        $this->aSamples = Matrix::gaussian(self::NUM_SAMPLES, 8)->multiply($mask)->asArray();
        $mask = Matrix::rand(self::NUM_SAMPLES, 8)->greater(0.5);
        $this->bSamples = Matrix::gaussian(self::NUM_SAMPLES, 8)->multiply($mask)->asArray();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @BeforeMethods({"setUp", "setUpSparse"})
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function computeSparse() : void
    {
        \array_map([$this->kernel, 'compute'], $this->aSamples, $this->bSamples);
    }
    public function setUpVerySparse() : void
    {
        $mask = Matrix::rand(self::NUM_SAMPLES, 8)->greater(0.9);
        $this->aSamples = Matrix::gaussian(self::NUM_SAMPLES, 8)->multiply($mask)->asArray();
        $mask = Matrix::rand(self::NUM_SAMPLES, 8)->greater(0.9);
        $this->bSamples = Matrix::gaussian(self::NUM_SAMPLES, 8)->multiply($mask)->asArray();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @BeforeMethods({"setUp", "setUpVerySparse"})
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function computeVerySparse() : void
    {
        \array_map([$this->kernel, 'compute'], $this->aSamples, $this->bSamples);
    }
}
