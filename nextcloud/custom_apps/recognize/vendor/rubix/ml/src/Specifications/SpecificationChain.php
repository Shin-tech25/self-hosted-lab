<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Specifications;

/**
 * @internal
 */
class SpecificationChain extends Specification
{
    /**
     * A list of specifications to check in order.
     *
     * @var iterable<\OCA\Recognize\Vendor\Rubix\ML\Specifications\Specification>
     */
    protected iterable $specifications;
    /**
     * Build a specification object with the given arguments.
     *
     * @param iterable<\OCA\Recognize\Vendor\Rubix\ML\Specifications\Specification> $specifications
     * @return self
     */
    public static function with(iterable $specifications) : self
    {
        return new self($specifications);
    }
    /**
     * @param iterable<\OCA\Recognize\Vendor\Rubix\ML\Specifications\Specification> $specifications
     * @throws \OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException
     */
    public function __construct(iterable $specifications)
    {
        $this->specifications = $specifications;
    }
    /**
     * Perform a check of the specification and throw an exception if invalid.
     */
    public function check() : void
    {
        foreach ($this->specifications as $specification) {
            $specification->check();
        }
    }
}
