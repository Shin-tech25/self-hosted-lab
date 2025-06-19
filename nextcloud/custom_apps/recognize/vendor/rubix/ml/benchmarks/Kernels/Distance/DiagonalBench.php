<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Kernels\Distance;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Diagonal;
/**
 * @Groups({"DistanceKernels"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class DiagonalBench
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
     * @var Diagonal
     */
    protected $kernel;
    public function setUp() : void
    {
        $generator = new Blob([0, 0, 0, 0, 0, 0, 0, 0], 5.0);
        $this->aSamples = $generator->generate(self::NUM_SAMPLES)->samples();
        $this->bSamples = $generator->generate(self::NUM_SAMPLES)->samples();
        $this->kernel = new Diagonal();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function compute() : void
    {
        \array_map([$this->kernel, 'compute'], $this->aSamples, $this->bSamples);
    }
}
