<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Specifications;

use Exception;
/**
 * Specification
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
abstract class Specification
{
    /**
     * Perform a check of the specification and throw an exception if invalid.
     *
     * @throws \OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException
     */
    public abstract function check() : void;
    /**
     * Does the specification pass?
     *
     * @return bool
     */
    public function passes() : bool
    {
        try {
            $this->check();
            return \true;
        } catch (Exception $exception) {
            return \false;
        }
    }
    /**
     * Does the specification fail?
     *
     * @return bool
     */
    public function fails() : bool
    {
        return !$this->passes();
    }
}
